<?php

namespace App\Console\Commands;

use App\Models\LoanAccount;
use App\Services\LoanOperationService;
use Illuminate\Console\Command;

/**
 * php artisan loan:auto-debit [--date=YYYY-MM-DD]
 *
 * Jalankan auto-debit cicilan kredit dari rekening tabungan nasabah
 * untuk semua pinjaman ACTIVE/NPL yang memiliki angsuran jatuh tempo.
 *
 * Idealnya dijadwalkan harian di App\Console\Kernel:
 *   $schedule->command('loan:auto-debit')->everyMinute();
 */
class LoanAutoDebitCommand extends Command
{
    protected $signature   = 'loan:auto-debit {--date= : Tanggal referensi (Y-m-d), default: hari ini}';
    protected $description = 'Proses auto-debit cicilan kredit dari rekening tabungan nasabah yang jatuh tempo';

    public function handle(LoanOperationService $loanService): int
    {
        $date = $this->option('date') ?? now()->toDateString();
        $this->info("🏦 [loan:auto-debit] Referensi tanggal: {$date}");

        $loans = LoanAccount::with(['savingAccount.product', 'schedules', 'product'])
            ->whereIn('status', ['ACTIVE', 'NPL'])
            ->whereNotNull('saving_account_id')
            ->get();

        if ($loans->isEmpty()) {
            $this->info('✅ Tidak ada pinjaman aktif dengan rekening tabungan terdaftar.');
            return self::SUCCESS;
        }

        $processed = 0;
        $skipped   = 0;
        $errors    = 0;

        $bar = $this->output->createProgressBar($loans->count());
        $bar->start();

        foreach ($loans as $loan) {
            try {
                $savingAccount = $loan->savingAccount;
                if (!$savingAccount || $savingAccount->status !== 'ACTIVE') {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Cek apakah ada angsuran yang jatuh tempo pada atau sebelum tanggal referensi
                $hasDue = $loan->schedules()
                    ->whereIn('status', ['UNPAID', 'PARTIAL'])
                    ->whereDate('due_date', '<=', $date)
                    ->exists();

                if (!$hasDue) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $loanService->processAutoDebit($savingAccount, $date);
                $processed++;
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->error("   ❌ Gagal [{$loan->account_no}]: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Diproses', 'Dilewati', 'Error'],
            [[$processed, $skipped, $errors]]
        );

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
