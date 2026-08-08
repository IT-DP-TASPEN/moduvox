<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SavingAccount;
use App\Models\NotasOwnership;
use App\Models\User;

class ProcessSavingAccounts extends Command
{
    protected $signature = 'saving:process {--batch=50}';
    protected $description = 'Proses SavingAccount approved_mitra: cek notas ownership, generate nomor buku tabungan.';

    public function handle()
    {
        $batchSize = (int) $this->option('batch');

        $this->info("🚀 Mulai proses batch {$batchSize} SavingAccount (approved_mitra)...");

        $requests = SavingAccount::where('status', 'approved_mitra')
            ->limit($batchSize)
            ->get();

        if ($requests->isEmpty()) {
            $this->warn('Tidak ada data berstatus approved_mitra.');
            return;
        }

        foreach ($requests as $saving) {
            DB::transaction(function () use ($saving) {

                $user = User::lockForUpdate()->find($saving->created_by);
                $mitraMasterId = $user?->mitra_master_id;

                if (!$mitraMasterId) {
                    $saving->update([
                        'status' => 'failed',
                        'keterangan_2' => 'User tidak punya mitra_master_id',
                    ]);
                    return;
                }

                $exists = NotasOwnership::where('notas', $saving->notas)
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    $saving->update([
                        'status' => 'failed',
                        'keterangan_2' => 'NOTAS SUDAH TERDAFTAR',
                    ]);
                    return;
                }

                $saving->update([
                    'status' => 'on_process',
                    'keterangan_2' => 'NOTAS BELUM TERDAFTAR, MENUNGGU PROSES SELANJUTNYA. ',
                ]);
            });
        }

        $this->info('🏁 Batch selesai. Semua data siap untuk tahap berikutnya.');
    }
}
