<?php

namespace App\Console\Commands;


use App\Models\SavingAccountInternal;
use Illuminate\Console\Command;
use App\Models\NotasOwnership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessCreateSavingInternal extends Command
{
    protected $signature = 'app:process-create-saving-internal {--batch=50}';
    protected $description = 'Proses create rekening tabungan untuk nasabah yang sudah berhasil create CIF';

    public function handle()
    {
        $batchSize = (int) $this->option('batch');

        $this->info("🚀 Mulai proses create SAVING untuk {$batchSize} data...");

        SavingAccountInternal::query()
            ->where('status', 'on_process')
            ->where('status_cif', 'success')     // CIF sudah berhasil
            ->where('status_saving', 'on_process') // Saving belum dibuat
            ->chunkById($batchSize, function ($accounts) {

                foreach ($accounts as $model) {

                    DB::transaction(function () use ($model) {
                        $this->processSaving($model);
                        $model->save();
                    });

                    $this->info("✅ Proses selesai untuk: {$model->customer_name} ({$model->status_saving})");
                }
            });

        $this->info("🏁 Semua batch proses saving selesai.");
    }


    /**
     * STEP 2 — Create Saving Account Only
     */
    private function processSaving(SavingAccountInternal $model)
    {
        try {

            if (!$model->customer_id) {
                $model->status_saving = 'failed';
                $model->keterangan_2 = 'Gagal create saving — customer_id kosong (CIF belum ada).';
                return;
            }

            $savingBody = [
                "cifNumber" => $model->customer_id,
                "productCode" => $model->savingProduct->product_code,
                "alias" => "",
                "openingPurpose" => $model->saving_perpouse,
                "alternateNumber" => "",
                "branchCode" => $model->branch_code,
                "facilityValue" => "",
            ];

            $savingResponse = $this->sendSignedRequest(
                config('services.api.create_saving_url'),
                $savingBody
            );

            if (!$savingResponse['success']) {
                $model->status_saving = 'failed';
                $model->keterangan_2 = "Create saving gagal: {$savingResponse['message']}";
                return;
            }

            $accountNumber = $savingResponse['data']['accountNumber'] ?? null;

            if (!$accountNumber) {
                $model->status_saving = 'failed';
                $model->keterangan_2 = 'Account number tidak ditemukan di response.';
                return;
            }

            $model->rek_tabungan = $accountNumber;
            $model->saving_booknumber = $this->generateBookNumber();
            $model->status_saving = 'success';
            $model->status = 'success';
            $model->keterangan_2 = 'Rekening tabungan berhasil dibuat.';

            // Insert ke Notas Ownership
            $user = User::find($model->created_by);
            $mitraMasterId = $model->mitraMaster?->id;

            if ($mitraMasterId) {
                NotasOwnership::firstOrCreate([
                    'notas' => $model->notas,
                ], [
                    'mitra_master_id' => $mitraMasterId,
                    'nama_nasabah' => $model->customer_name,
                    'rek_tabungan' => $accountNumber,
                    'rek_replace' => $accountNumber,
                ]);
            }
        } catch (\Throwable $e) {

            $model->status_saving = 'failed';
            $model->keterangan_2 = 'Exception Saving: ' . $e->getMessage();

            Log::error("❌ Error create saving ID {$model->id}: {$e->getMessage()}");
        }
    }


    /**
     * Kirim request signed (HMAC SHA256)
     */
    private function sendSignedRequest(string $url, array $body): array
    {
        $bodyString = json_encode($body, JSON_UNESCAPED_SLASHES);
        $secretKey = config('services.api.secret_key');
        $signature = hash_hmac('sha256', $bodyString, $secretKey);

        $response = Http::withHeaders([
            'Signature' => $signature,
            'Content-Type' => 'application/json',
        ])->withBody($bodyString, 'application/json')->post($url);

        if (!$response->ok()) {
            return ['success' => false, 'message' => 'HTTP Error ' . $response->status()];
        }

        $json = $response->json();
        return [
            'success' => ($json['responseCode'] ?? null) === '00',
            'message' => $json['description'] ?? 'No description',
            'data' => $json['data'] ?? [],
        ];
    }


    /**
     * Generate nomor buku tabungan
     */
    private function generateBookNumber(): string
    {
        return DB::transaction(function () {

            $sequence = DB::table('saving_account_sequence_internal')->lockForUpdate()->first();

            if (!$sequence) {
                DB::table('saving_account_sequence_internal')->insert([
                    'last_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sequence = (object) ['last_number' => 0];
            }

            $next = (int) $sequence->last_number + 1;

            DB::table('saving_account_sequence_internal')->update([
                'last_number' => $next,
                'updated_at' => now(),
            ]);

            return 'SP' . str_pad($next, 15, '0', STR_PAD_LEFT);
        });
    }
}
