<?php

namespace App\Console\Commands;

use App\Models\DepositAccount;
use App\Services\DepositOperationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

/**
 * php artisan deposit:process-maturity [--date=YYYY-MM-DD]
 *
 * Proses deposito jatuh tempo:
 *   - NONE (Non-ARO):           Cairkan pokok ke rekening tabungan → tutup deposito.
 *   - PRINCIPAL:                Roll over pokok saja → bayar bunga → buat deposito baru.
 *   - PRINCIPAL_INTEREST:       Roll over pokok + bunga → buat deposito baru (bunga terakumulasi).
 *
 * Idealnya dijadwalkan harian setelah deposit:pay-interest:
 *   $schedule->command('deposit:process-maturity')->dailyAt('08:00');
 */
class DepositProcessMaturityCommand extends Command
{
    protected $signature = 'deposit:process-maturity {--date= : Tanggal referensi (Y-m-d), default: hari ini}';
    protected $description = 'Proses deposito jatuh tempo: Non-ARO dicairkan, ARO diperpanjang otomatis';

    public function handle(DepositOperationService $depositService): int
    {
        $dateOption = $this->option('date');
        $date = $dateOption ?: config('app.business_date') ?: now()->toDateString();
        $this->info("🏦 [deposit:process-maturity] Referensi tanggal: {$date}");

        if (! $dateOption) {
            return $this->process($depositService, $date);
        }

        Date::setTestNow(Carbon::parse($date)->endOfDay());

        try {
            return $this->process($depositService, $date);
        } finally {
            Date::setTestNow();
        }
    }

    private function process(DepositOperationService $depositService, string $date): int
    {
        $accounts = DepositAccount::with(['product', 'savingAccount.product', 'schedules'])
            ->where('status', 'ACTIVE')
            ->whereDate('maturity_date', '<=', $date)
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('✅ Tidak ada deposito yang jatuh tempo.');
            return self::SUCCESS;
        }

        $this->info("📋 Ditemukan {$accounts->count()} deposito jatuh tempo.");

        $closed = $rolled = $errors = 0;

        $bar = $this->output->createProgressBar($accounts->count());
        $bar->start();

        foreach ($accounts as $account) {
            try {
                $result = $depositService->processMaturity($account);

                if ($result === 'CLOSED') {
                    $closed++;
                } else {
                    $rolled++;
                }
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->error(
                    "   ❌ Gagal [{$account->account_no}]: " . $e->getMessage()
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Non-ARO Dicairkan', 'ARO Diperpanjang', 'Error'],
            [[$closed, $rolled, $errors]]
        );

        if ($closed > 0)
            $this->info("✅ {$closed} deposito Non-ARO berhasil dicairkan.");
        if ($rolled > 0)
            $this->info("🔄 {$rolled} deposito ARO berhasil diperpanjang.");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
