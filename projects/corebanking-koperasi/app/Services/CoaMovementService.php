<?php

namespace App\Services;

use App\Models\CoaMovement;
use App\Models\Coa;
use App\Models\Journal;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CoaMovementService
{
    public function syncForJournal(Journal $journal): void
    {
        $journal->loadMissing('entries');

        $coaIds = $journal->entries
            ->pluck('coa_id')
            ->filter()
            ->unique()
            ->values();

        if ($coaIds->isEmpty()) {
            return;
        }

        foreach ($coaIds as $coaId) {
            $this->rebuildFromDate((int) $journal->branch_id, (int) $coaId, $journal->transaction_date);
        }
    }

    public function syncForJournalEntry(JournalEntry $entry): void
    {
        $entry->loadMissing('journal');
        if (!$entry->journal) {
            return;
        }

        $this->rebuildFromDate((int) $entry->journal->branch_id, (int) $entry->coa_id, $entry->journal->transaction_date);
    }

    public function rebuildFromDate(int $branchId, int $coaId, $fromDate): void
    {
        $startDate = Carbon::parse($fromDate)->toDateString();

        DB::transaction(function () use ($branchId, $coaId, $startDate) {
            $coaType = Coa::query()->whereKey($coaId)->value('type');
            $debitNormal = in_array($coaType, ['ASSET', 'EXPENSE'], true);

            $movementDates = CoaMovement::query()
                ->where('branch_id', $branchId)
                ->where('coa_id', $coaId)
                ->whereDate('transaction_date', '>=', $startDate)
                ->pluck('transaction_date')
                ->map(fn($d) => Carbon::parse($d)->toDateString());

            $journalDates = JournalEntry::query()
                ->selectRaw('DATE(journals.transaction_date) as txn_date')
                ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
                ->where('journals.status', 'APPROVED')
                ->where('journals.branch_id', $branchId)
                ->where('journal_entries.coa_id', $coaId)
                ->whereDate('journals.transaction_date', '>=', $startDate)
                ->groupByRaw('DATE(journals.transaction_date)')
                ->orderByRaw('DATE(journals.transaction_date)')
                ->pluck('txn_date')
                ->map(fn($d) => Carbon::parse($d)->toDateString());

            $dates = $movementDates
                ->merge($journalDates)
                ->unique()
                ->sort()
                ->values();

            if ($dates->isEmpty()) {
                return;
            }

            $previousEnding = (float) (CoaMovement::query()
                ->where('branch_id', $branchId)
                ->where('coa_id', $coaId)
                ->whereDate('transaction_date', '<', $dates->first())
                ->orderByDesc('transaction_date')
                ->value('ending_balance') ?? 0);

            foreach ($dates as $date) {
                $daily = $this->dailyTotals($branchId, $coaId, $date);
                $debit = (float) $daily['debit'];
                $credit = (float) $daily['credit'];

                if (abs($debit) < 0.0001 && abs($credit) < 0.0001) {
                    CoaMovement::query()
                        ->where('branch_id', $branchId)
                        ->where('coa_id', $coaId)
                        ->whereDate('transaction_date', $date)
                        ->delete();
                    continue;
                }

                $ending = $debitNormal
                    ? $previousEnding + $debit - $credit
                    : $previousEnding + $credit - $debit;

                CoaMovement::updateOrCreate(
                    [
                        'branch_id' => $branchId,
                        'coa_id' => $coaId,
                        'transaction_date' => $date,
                    ],
                    [
                        'starting_balance' => $previousEnding,
                        'debit' => $debit,
                        'credit' => $credit,
                        'ending_balance' => $ending,
                    ]
                );

                $previousEnding = $ending;
            }
        });
    }

    private function dailyTotals(int $branchId, int $coaId, string $date): array
    {
        return (array) JournalEntry::query()
            ->selectRaw('COALESCE(SUM(journal_entries.debit),0) as debit, COALESCE(SUM(journal_entries.credit),0) as credit')
            ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
            ->where('journals.status', 'APPROVED')
            ->where('journals.branch_id', $branchId)
            ->where('journal_entries.coa_id', $coaId)
            ->whereDate('journals.transaction_date', $date)
            ->first()
            ?->toArray() ?? ['debit' => 0, 'credit' => 0];
    }
}
