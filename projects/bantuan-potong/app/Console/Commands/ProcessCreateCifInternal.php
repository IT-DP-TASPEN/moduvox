<?php

namespace App\Console\Commands;

use App\Models\SavingAccountInternal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessCreateCifInternal extends Command
{
    protected $signature = 'app:process-create-cif-internal {--batch=50}';
    protected $description = 'Proses create CIF saja untuk SavingAccount yang status_cif = on_process';

    public function handle()
    {
        $batchSize = (int) $this->option('batch');

        $this->info("🚀 Mulai proses CREATE CIF untuk {$batchSize} data...");

        SavingAccountInternal::query()
            ->where('status', 'on_process')
            ->where('status_cif', 'on_process')
            ->chunkById($batchSize, function ($accounts) {
                foreach ($accounts as $model) {
                    DB::transaction(function () use ($model) {
                        $this->processCif($model);
                        $model->save();
                    });

                    $this->info("➡️ {$model->customer_name} → {$model->status_cif}");
                }
            });

        $this->info("🏁 Proses selesai.");
    }

    private function processCif(SavingAccountInternal $model)
    {
        try {
            $body = [
                "name" => $model->customer_name,
                "mobilePhoneNo" => $model->mobile_phone,
                "nationalIdNo" => $model->national_id_number,
                "birthDate" => $model->date_of_birth,
                "gender" => $model->gender,
                "email" => "",
                "taxIdNo" => $model->tax_id ?? "",
                "nationality" => "WNI",
                "birthPlace" => $model->place_of_birth,
                "motherMaidenName" => $model->mother_maiden_name,
                "religion" => $model->religion,
                "province" => $model->provinceMaster?->nama,
                "city" => $model->dati2_name,
                "postalCode" => $model->postal_code ?? "",
                "subDistrict" => $model->sub_district,
                "village" => $model->urban_village,
                "address" => $model->address,
                "maritalStatus" => $model->marital_status,
                "lastEducation" => $model->last_edu,
                "job" => "099",
                "branchCode" => $model->branch_code,
                "cityDati2" => $model->dati2_code,
            ];

            $response = $this->sendSignedRequest(
                config('services.api.create_cif_url'),
                $body
            );

            if (!$response['success']) {
                $model->status = 'failed';
                $model->status_cif = 'failed';
                $model->keterangan_2 = "CIF gagal: {$response['message']}";
                return;
            }

            $cifNumber = $response['data']['cifNumber'] ?? null;

            if (!$cifNumber) {
                $model->status = 'failed';
                $model->status_cif = 'failed';
                $model->keterangan_2 = 'CIF Number tidak ditemukan.';
                return;
            }

            $model->customer_id = $cifNumber;
            $model->status_cif = 'success';
            $model->keterangan_2 = 'CIF berhasil dibuat.';
        } catch (\Throwable $e) {
            $model->status = 'failed';
            $model->status_cif = 'failed';
            $model->keterangan_2 = 'Exception: ' . $e->getMessage();

            Log::error("❌ Error CREATE CIF ID {$model->id}: {$e->getMessage()}");
        }
    }

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
            'data'    => $json['data'] ?? [],
        ];
    }
}
