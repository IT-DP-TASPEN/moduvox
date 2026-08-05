<?php

namespace App\Console\Commands;

use App\Models\DepositSchedule;
use App\Services\DepositOperationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

/**
 * php artisan deposit:pay-interest [--date=YYYY-MM-DD]
 *
 * Cairkan bunga simpanan berjangka ke rekening tabungan nasabah
 * untuk semua jadwal yang jatuh tempo pada tanggal referensi.
 *
 * Idealnya dijadwalkan harian di App\Console\Kernel:
 *   $schedule->command('deposit:pay-interest')->dailyAt('07:30');
 */
class DepositPayInterestCommand extends Command
{
    protected $signature   = 'deposit:pay-interest {--date= : Tanggal referensi (Y-m-d), default: hari ini}';
    protected $description = 'Cairkan bunga simpanan berjangka jatuh tempo ke rekening tabungan nasabah';

    public function handle(DepositOperationService $depositService): int
    {
        $dateOption = $this->option('date');
        $date = $dateOption ?? config('app.business_date') ?? now()->toDateString();
        $this->info("🏦 [deposit:pay-interest] Referensi tanggal: {$date}");

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
        $schedules = DepositSchedule::with(['account.product', 'account.savingAccount.product'])
            ->where('status', 'PENDING')
            ->whereDate('schedule_date', '<=', $date)
            ->whereHas('account', fn ($q) => $q->whereIn('status', ['ACTIVE', 'MATURED']))
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('✅ Tidak ada jadwal bunga simpanan berjangka yang jatuh tempo.');
            return self::SUCCESS;
        }

        $this->info("📋 Ditemukan {$schedules->count()} jadwal bunga yang akan diproses.");

        $paid   = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($schedules->count());
        $bar->start();

        foreach ($schedules as $schedule) {
            try {
                $depositService->disbursePeriodInterest($schedule);
                $paid++;
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->error(
                    "   ❌ Gagal [DepositSchedule #{$schedule->id} — {$schedule->account->account_no}]: "
                    . $e->getMessage()
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Berhasil Dibayar', 'Error'],
            [[$paid, $errors]]
        );

        if ($paid > 0) {
            $this->info("✅ Total bunga yang dicairkan: {$paid} jadwal.");
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
