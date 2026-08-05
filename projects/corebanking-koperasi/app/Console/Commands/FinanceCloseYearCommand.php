<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Coa;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Services\JournalService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinanceCloseYearCommand extends Command
{
    protected $signature = 'finance:close-year
        {year : Tahun buku yang akan ditutup}
        {--branch= : ID cabang. Kosongkan untuk semua cabang}
        {--execute : Posting jurnal closing}';

    protected $description = 'Tutup akun pendapatan dan beban tahunan ke COA SHU laba tahun lalu';

    public function handle(JournalService $journalService): int
    {
        $year = (int) $this->argument('year');
        $execute = (bool) $this->option('execute');
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";
        $closingDate = ($year + 1) . '-01-01';
        $shuLastYearCoa = Coa::where('coa_code', '315000')->firstOrFail();
        $branches = $this->branches();

        foreach ($branches as $branch) {
            $forceRef = "CLS{$year}" . str_pad((string) $branch->id, 4, '0', STR_PAD_LEFT);

            if (Journal::where('reference_no', $forceRef)->exists()) {
                $this->warn("{$branch->name}: jurnal closing {$forceRef} sudah ada, dilewati.");
                continue;
            }

            $balances = $this->profitLossBalances($startDate, $endDate, (int) $branch->id);
            $revenue = round($balances->filter(fn ($entry) => $entry->coa->type === 'REVENUE')->sum('balance'), 2);
            $expense = round($balances->filter(fn ($entry) => $entry->coa->type === 'EXPENSE')->sum('balance'), 2);
            $profit = round($revenue - $expense, 2);
            $entries = $this->closingEntries($balances, $shuLastYearCoa->id, $profit);

            $this->table(['Cabang', 'Tanggal Jurnal', 'Pendapatan', 'Beban', 'Laba/Rugi', 'Ref'], [[
                $branch->name,
                $closingDate,
                $this->money($revenue),
                $this->money($expense),
                $this->money($profit),
                $forceRef,
            ]]);

            if (empty($entries)) {
                $this->line("{$branch->name}: tidak ada saldo pendapatan/beban untuk ditutup.");
                continue;
            }

            if (! $execute) {
                $this->warn('PREVIEW saja. Tambahkan --execute untuk posting jurnal closing.');
                continue;
            }

            $journal = $journalService->createSystemJournal(
                branchId: (int) $branch->id,
                prefix: 'CLS',
                description: "Closing pendapatan dan beban tahun {$year} ke SHU laba tahun lalu",
                entries: $entries,
                date: $closingDate,
                forceRef: $forceRef,
            );

            $this->info("{$branch->name}: jurnal closing berhasil dibuat {$journal->reference_no}.");
        }

        return self::SUCCESS;
    }

    private function branches(): Collection
    {
        return Branch::query()
            ->when($this->option('branch'), fn ($query, $branchId) => $query->whereKey($branchId))
            ->orderBy('id')
            ->get();
    }

    private function profitLossBalances(string $startDate, string $endDate, int $branchId): Collection
    {
        return JournalEntry::query()
            ->select('coa_id', DB::raw('SUM(debit) as debit'), DB::raw('SUM(credit) as credit'))
            ->with('coa:id,coa_code,name,type')
            ->whereHas('coa', fn ($query) => $query
                ->whereIn('type', ['REVENUE', 'EXPENSE'])
                ->where('is_leaf', true))
            ->whereHas('journal', fn ($query) => $query
                ->where('status', 'APPROVED')
                ->where('branch_id', $branchId)
                ->whereDate('transaction_date', '>=', $startDate)
                ->whereDate('transaction_date', '<=', $endDate)
                ->where('reference_no', 'not like', 'CLS%'))
            ->groupBy('coa_id')
            ->get()
            ->map(function (JournalEntry $entry) {
                $entry->balance = $entry->coa->type === 'EXPENSE'
                    ? round((float) $entry->debit - (float) $entry->credit, 2)
                    : round((float) $entry->credit - (float) $entry->debit, 2);

                return $entry;
            })
            ->filter(fn (JournalEntry $entry) => abs((float) $entry->balance) > 0.01)
            ->values();
    }

    private function closingEntries(Collection $balances, int $shuLastYearCoaId, float $profit): array
    {
        $entries = [];

        foreach ($balances as $entry) {
            $balance = (float) $entry->balance;

            if ($entry->coa->type === 'REVENUE' && $balance > 0) {
                $entries[] = ['coa_id' => $entry->coa_id, 'debit' => $balance, 'credit' => 0];
            } elseif ($entry->coa->type === 'REVENUE') {
                $entries[] = ['coa_id' => $entry->coa_id, 'debit' => 0, 'credit' => abs($balance)];
            } elseif ($balance > 0) {
                $entries[] = ['coa_id' => $entry->coa_id, 'debit' => 0, 'credit' => $balance];
            } else {
                $entries[] = ['coa_id' => $entry->coa_id, 'debit' => abs($balance), 'credit' => 0];
            }
        }

        if ($profit > 0) {
            $entries[] = ['coa_id' => $shuLastYearCoaId, 'debit' => 0, 'credit' => abs($profit)];
        } elseif ($profit < 0) {
            $entries[] = ['coa_id' => $shuLastYearCoaId, 'debit' => abs($profit), 'credit' => 0];
        }

        return $entries;
    }

    private function money(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
    }
}
