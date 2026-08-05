<?php

namespace App\Services;

use App\Models\ApiJournal;
use App\Models\ApiLog;
use App\Models\PenyusutanBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Enums\JournalState;

class FinCloudApiService
{
    /**
     * Generate Jurnal API records from an approved batch.
     * This prepares the payload grouped by Kantor and Golongan.
     */
    public static function generateJournals(PenyusutanBatch $batch)
    {
        // Get grouped summary
        $summary = \Illuminate\Support\Facades\DB::table('penyusutan_detail')
            ->join('inventaris', 'penyusutan_detail.inventaris_id', '=', 'inventaris.id')
            ->join('mst_golongan', 'inventaris.golongan_id', '=', 'mst_golongan.id')
            ->join('mst_kantor', 'penyusutan_detail.kantor_id', '=', 'mst_kantor.id')
            ->where('penyusutan_detail.batch_id', $batch->id)
            ->select(
                'mst_kantor.kode as kode_kantor',
                'mst_golongan.kode as kode_golongan',
                'mst_golongan.nama as nama_golongan',
                'mst_golongan.akun_debet',
                'mst_golongan.akun_kredit',
                \Illuminate\Support\Facades\DB::raw('SUM(beban_bulan_ini) as total_beban')
            )
            ->groupBy('mst_kantor.kode', 'mst_golongan.kode', 'mst_golongan.nama')
            ->get();

        $periodeDate = Carbon::createFromFormat('Ym', $batch->periode_ym)->endOfMonth();

        foreach ($summary as $row) {
            if ((float)$row->total_beban <= 0) continue;

            $reference = self::generateReference($row->kode_kantor, $periodeDate);
            $payload = self::buildPayload($row, $reference, $periodeDate);

            ApiJournal::updateOrCreate(
                [
                    'batch_id' => $batch->id,
                    'reff_id' => $reference,
                ],
                [
                    'payload' => $payload,
                    'state' => JournalState::DRAFT->value,
                    'retry_count' => 0,
                ]
            );
        }
    }

    /**
     * Send all DRAFT or RETRY journals for a batch
     */
    public static function processJournals(PenyusutanBatch $batch)
    {
        $journals = ApiJournal::where('batch_id', $batch->id)
                        ->whereIn('state', [JournalState::DRAFT->value, JournalState::RETRY->value])
                        ->get();

        foreach ($journals as $journal) {
            self::sendJournal($journal);
        }
    }

    /**
     * Send a single Journal to FinCloud
     */
    public static function sendJournal(ApiJournal $journal)
    {
        $journal->transitionTo(JournalState::SENDING);

        $endpoint = config('services.api.gl_endpoint', 'https://mock.fincloud.local/api/gl');
        $secret = config('services.api.gl_secret', 'secret-key-123');
        $isMock = config('services.api.mock_mode', true);
        
        $payload = $journal->payload;
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $jsonPayload, $secret);

        $startTime = microtime(true);
        $statusCode = 500;
        $responseBody = null;

        try {
            if ($isMock || $isMock === '1' || $isMock === 'true') {
                // Mock behavior
                $statusCode = 200;
                $responseBody = [
                    'responseCode' => '00',
                    'description' => 'MOCK_SUCCESS',
                    'data' => [
                        'journalId' => 'JRN-' . rand(100000, 999999)
                    ]
                ];
                usleep(500000); // simulate delay
            } else {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Signature' => $signature
                ])->timeout(20)->post($endpoint, $payload);
                
                $statusCode = $response->status();
                $responseBody = $response->json() ?? ['raw_body' => $response->body()];
            }

            $durationMs = round((microtime(true) - $startTime) * 1000);

            // Log the request
            ApiLog::create([
                'journal_id' => $journal->id,
                'endpoint' => $endpoint,
                'method' => 'POST',
                'request_payload' => $payload,
                'response_payload' => $responseBody,
                'http_status' => $statusCode,
                'duration_ms' => $durationMs,
            ]);

            // Update Journal State
            if ($statusCode >= 200 && $statusCode < 300 && isset($responseBody['responseCode']) && $responseBody['responseCode'] === '00') {
                $journal->response_body = json_encode($responseBody);
                $journal->core_reff = $responseBody['data']['journalId'] ?? null;
                $journal->transitionTo(JournalState::SUCCESS);
            } else {
                $journal->response_body = json_encode($responseBody);
                $journal->transitionTo(JournalState::FAILED);
            }

        } catch (\Exception $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000);
            
            ApiLog::create([
                'journal_id' => $journal->id,
                'endpoint' => $endpoint,
                'method' => 'POST',
                'request_payload' => $payload,
                'response_payload' => ['error' => $e->getMessage()],
                'http_status' => 0,
                'duration_ms' => $durationMs,
            ]);

            $journal->response_body = json_encode(['error' => 'Network/System Error', 'message' => $e->getMessage()]);
            $journal->transitionTo(JournalState::FAILED);
        }
    }

    private static function generateReference(string $kodeKantor, Carbon $date): string
    {
        $branch = str_pad($kodeKantor, 2, '0', STR_PAD_LEFT);
        $ym = $date->format('ym');
        $prefix = 'IV-' . $branch . $ym;

        // Find the highest existing sequence number for this prefix
        $lastRef = ApiJournal::where('reff_id', 'like', $prefix . '%')
            ->orderByDesc('reff_id')
            ->value('reff_id');

        if ($lastRef) {
            $lastSeq = (int)substr($lastRef, strlen($prefix));
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return sprintf('%s%02d', $prefix, $nextSeq);
    }

    private static function buildPayload($data, string $reference, Carbon $date): array
    {
        $branch3 = str_pad($data->kode_kantor, 3, '0', STR_PAD_LEFT);
        $amount = number_format((float)$data->total_beban, 2, ',', '');
        $golCode = $data->kode_golongan;
        $trxType = self::resolveTrxType($golCode);
        $golText = self::mapGolonganName($golCode);

        return [
            'referenceNumber' => $reference,
            'trxType'         => $trxType,
            'termType'        => '',
            'termId'          => 'FINCLOUD',
            'receiptNumber'   => $reference,
            'debitAccount'    => $data->akun_debet ?? '',
            'creditAccount'   => $data->akun_kredit ?? '',
            'amount'          => $amount,
            'fee'             => '0',
            'creditFee'       => '0',
            'branchCode'      => $branch3,
            'debitNarrative'  => 'Penyusutan Debit',
            'creditNarrative' => 'Penyusutan Kredit',
            'customerId'      => '127369366000',
            'dateTime'        => $date->format('Ymd') . '000000',
            'description'     => "{$branch3}.Penyusutan_Asset.{$golText}",
            'debitFee'        => '0',
            'destAccount'     => '',
            'currency'        => 'IDR',
            'srcAccType'      => '10',
            'totalBill'       => '',
            'type'            => 'G2',
        ];
    }

    /**
     * Resolve trxType berdasarkan kode golongan — sesuai format API Core FinCloud.
     */
    private static function resolveTrxType(string $kodeGolongan): string
    {
        return match ($kodeGolongan) {
            '06' => 'Deprc-Buildings',
            '05' => 'Deprc-Intangible Assets',
            '02', '03', '04' => 'Deprc-Equipments',
            default => 'Deprc-Equipments',
        };
    }

    /**
     * Map kode golongan ke nama singkat untuk description.
     */
    private static function mapGolonganName(string $kodeGolongan): string
    {
        return match ($kodeGolongan) {
            '06' => 'Golongan I',
            '02', '03', '04' => 'Golongan II',
            '05' => 'Golongan III',
            default => "Gol {$kodeGolongan}",
        };
    }
}

