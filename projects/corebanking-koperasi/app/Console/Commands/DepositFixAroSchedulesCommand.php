<?php

namespace App\Console\Commands;

use App\Models\DepositAccount;
use App\Services\DepositOperationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class DepositFixAroSchedulesCommand extends Command
{
    protected $signature = 'deposit:fix-aro-schedules
        {--date= : Tanggal referensi (Y-m-d), default: APP_BUSINESS_DATE/hari ini}
        {--account= : Nomor rekening deposito tertentu}
        {--dry-run : Tampilkan kandidat tanpa membuat jadwal}';

    protected $description = 'Fix sekali jalan untuk deposito ARO aktif yang jadwal periode berikutnya belum terbentuk';

    public function handle(DepositOperationService $depositService): int
    {
        $date = $this->option('date') ?: config('app.business_date') ?: now()->toDateString();
        $dryRun = (bool) $this->option('dry-run');

        $this->info('[deposit:fix-aro-schedules] Referensi tanggal: ' . $date);
        if ($dryRun) {
            $this->warn('Mode dry-run: data tidak akan diubah.');
        }

        Date::setTestNow(Carbon::parse($date)->endOfDay());

        try {
            return $this->process($depositService, $date, $dryRun);
        } finally {
            Date::setTestNow();
        }
    }

    private function process(DepositOperationService $depositService, string $date, bool $dryRun): int
    {
        $accounts = DepositAccount::withCount([
            'schedules',
            'schedules as pending_schedules_count' => fn($query) => $query->where('status', 'PENDING'),
        ])
            ->whereIn('status', ['ACTIVE', 'MATURED'])
            ->whereIn('rollover_type', ['PRINCIPAL', 'PRINCIPAL_INTEREST'])
            ->whereDate('maturity_date', '<=', $date)
            ->whereDoesntHave('schedules', fn($query) => $query->where('status', 'PENDING'))
            ->when($this->option('account'), fn($query, $accountNo) => $query->where('account_no', $accountNo))
            ->orderBy('account_no')
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('Tidak ada rekening ARO yang perlu diperbaiki.');
            return self::SUCCESS;
        }

        $this->table(
            ['Rekening', 'Status', 'ARO', 'Tenor', 'Maturity', 'Jadwal'],
            $accounts->map(fn($account) => [
                $account->account_no,
                $account->status,
                $account->rollover_type,
                $account->tenor,
                $account->maturity_date?->toDateString(),
                $account->schedules_count,
            ])->all()
        );

        if ($dryRun) {
            return self::SUCCESS;
        }

        $fixed = $errors = 0;

        foreach ($accounts as $account) {
            try {
                $before = $account->schedules_count;
                $depositService->ensureRolloverSchedules($account, $date);
                $after = $account->fresh()->schedules()->count();

                $this->line("{$account->account_no}: jadwal {$before} -> {$after}");
                $fixed++;
            } catch (\Throwable $e) {
                $errors++;
                $this->error("{$account->account_no}: " . $e->getMessage());
            }
        }

        $this->table(['Fixed', 'Error'], [[$fixed, $errors]]);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
