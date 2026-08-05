<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Coa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * JournalService
 *
 * Handles all journal creation, reference number generation,
 * reversal, and manual journal posting.
 *
 * Reference format:
 *   JRN{YYYYMMDD}{4-digit} → JRN202605120001  (Manual Journal)
 *   REV{YYYYMMDD}{4-digit} → REV202605120001  (Reversal)
 *   SYS{YYYYMMDD}{4-digit} → SYS202605120001  (System Auto)
 *   LDS{YYYYMMDD}{4-digit} → LDS202605120001  (Loan Disbursement)
 *   LRP{YYYYMMDD}{4-digit} → LRP202605120001  (Loan Repayment)
 *   DEP{YYYYMMDD}{4-digit} → DEP202605120001  (Deposit Placement)
 *   SDP{YYYYMMDD}{4-digit} → SDP202605120001  (Saving Deposit)
 *   SDW{YYYYMMDD}{4-digit} → SDW202605120001  (Saving Withdrawal)
 */
class JournalService
{
    // ─────────────────────────────────────────────────────────────────────────
    // REFERENCE NUMBER GENERATOR
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate a unique sequential journal reference number.
     *
     * @param  string $prefix   e.g. 'JRN', 'REV', 'LDS', 'SDP'
     * @param  string|null $date  defaults to today (Y-m-d)
     * @return string  e.g. JRN202605120001
     */
    public function generateReferenceNo(string $prefix, ?string $date = null): string
    {
        $dateStr = $date
            ? Carbon::parse($date)->format('Ymd')
            : now()->format('Ymd');

        $pattern = $prefix . $dateStr . '%';

        // Use DB-level max to avoid race conditions under concurrent requests
        $lastRef = Journal::where('reference_no', 'like', $pattern)
            ->orderBy('reference_no', 'desc')
            ->value('reference_no');

        $sequence = 1;
        if ($lastRef) {
            $seqStr   = substr($lastRef, strlen($prefix) + 8); // 8 = YYYYMMDD
            $sequence = ((int) $seqStr) + 1;
        }

        return $prefix . $dateStr . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SYSTEM JOURNAL (auto-approved, used by operation services)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create a system journal (auto-approved).
     *
     * @param  int    $branchId
     * @param  string $prefix       e.g. 'SDP', 'LDS'
     * @param  string $description
     * @param  array  $entries      [['coa_id' => x, 'debit' => y, 'credit' => z], ...]
     * @param  string|null $date
     * @param  string|null $forceRef  Override reference number
     * @return Journal
     */
    public function createSystemJournal(
        int $branchId,
        string $prefix,
        string $description,
        array $entries,
        ?string $date = null,
        ?string $forceRef = null
    ): Journal {
        return DB::transaction(function () use ($branchId, $prefix, $description, $entries, $date, $forceRef) {
            $this->validateEntries($entries);

            $refNo = $forceRef ?? $this->generateReferenceNo($prefix, $date);

            $journal = Journal::create([
                'branch_id'       => $branchId,
                'reference_no'    => $refNo,
                'transaction_date' => $date ?? now()->toDateString(),
                'description'     => $description,
                'journal_type'    => 'SYSTEM',
                'status'          => 'APPROVED',
                'created_by'      => Auth::id() ?? \App\Models\User::getSystemUserId(),
                'approved_by'     => Auth::id() ?? \App\Models\User::getSystemUserId(),
                'approved_at'     => now(),
            ]);

            $this->postEntries($journal, $entries);

            return $journal;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MANUAL JOURNAL
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create a manual journal (PENDING, awaiting approval).
     *
     * @param  int    $branchId
     * @param  string $description
     * @param  array  $entries
     * @param  string|null $date
     * @param  string|null $revisionNotes
     * @return Journal
     */
    public function createManualJournal(
        int $branchId,
        string $description,
        array $entries,
        ?string $date = null,
        ?string $revisionNotes = null
    ): Journal {
        return DB::transaction(function () use ($branchId, $description, $entries, $date, $revisionNotes) {
            $this->validateEntries($entries);

            $refNo = $this->generateReferenceNo('JRN', $date);

            $journal = Journal::create([
                'branch_id'       => $branchId,
                'reference_no'    => $refNo,
                'transaction_date' => $date ?? now()->toDateString(),
                'description'     => $description,
                'revision_notes'  => $revisionNotes,
                'journal_type'    => 'MANUAL',
                'status'          => 'PENDING',
                'created_by'      => Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);

            $this->postEntries($journal, $entries);

            return $journal;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REVERSAL JOURNAL
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Reverse an existing journal by swapping all debit/credit entries.
     *
     * @param  Journal $originalJournal
     * @param  string  $reason
     * @param  bool    $autoApprove     If true, reversal is auto-approved (system reversals)
     * @return Journal  The reversal journal
     */
    public function reverseJournal(
        Journal $originalJournal,
        string $reason,
        bool $autoApprove = false
    ): Journal {
        return DB::transaction(function () use ($originalJournal, $reason, $autoApprove) {
            if ($originalJournal->journal_type === 'REVERSAL') {
                throw new \Exception('Jurnal reversal tidak dapat di-reverse kembali.');
            }

            $refNo = $this->generateReferenceNo('REV');

            $data = [
                'branch_id'           => $originalJournal->branch_id,
                'reference_no'        => $refNo,
                'transaction_date'    => now()->toDateString(),
                'description'         => "REVERSAL: [{$originalJournal->reference_no}] — {$reason}",
                'revision_notes'      => $reason,
                'journal_type'        => 'REVERSAL',
                'original_journal_id' => $originalJournal->id,
                'status'              => $autoApprove ? 'APPROVED' : 'PENDING',
                'created_by'          => Auth::id() ?? \App\Models\User::getSystemUserId(),
            ];

            if ($autoApprove) {
                $data['approved_by'] = Auth::id() ?? \App\Models\User::getSystemUserId();
                $data['approved_at'] = now();
            }

            $reversalJournal = Journal::create($data);

            // Flip every entry
            foreach ($originalJournal->entries as $entry) {
                JournalEntry::create([
                    'journal_id' => $reversalJournal->id,
                    'coa_id'     => $entry->coa_id,
                    'debit'      => $entry->credit,  // swap
                    'credit'     => $entry->debit,   // swap
                ]);
            }

            return $reversalJournal;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // APPROVE / REJECT MANUAL JOURNAL
    // ─────────────────────────────────────────────────────────────────────────

    public function approveJournal(Journal $journal): Journal
    {
        if ($journal->status !== 'PENDING') {
            throw new \Exception('Hanya jurnal PENDING yang dapat disetujui.');
        }

        $journal->update([
            'status'      => 'APPROVED',
            'approved_by' => Auth::id() ?? \App\Models\User::getSystemUserId(),
            'approved_at' => now(),
        ]);

        return $journal->fresh();
    }

    public function rejectJournal(Journal $journal, string $reason): Journal
    {
        if ($journal->status !== 'PENDING') {
            throw new \Exception('Hanya jurnal PENDING yang dapat ditolak.');
        }

        $journal->update([
            'status'         => 'REJECTED',
            'revision_notes' => $reason,
            'approved_by'    => Auth::id() ?? \App\Models\User::getSystemUserId(),
            'approved_at'    => now(),
        ]);

        return $journal->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Post journal entries from an array.
     *
     * @param  Journal $journal
     * @param  array   $entries  [['coa_id' => x, 'debit' => y, 'credit' => z], ...]
     */
    private function postEntries(Journal $journal, array $entries): void
    {
        foreach ($entries as $entry) {
            JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id'     => $entry['coa_id'],
                'reference_no' => $entry['reference_no'] ?? null,
                'description' => $entry['description'] ?? null,
                'debit'      => $entry['debit'] ?? 0,
                'credit'     => $entry['credit'] ?? 0,
            ]);
        }
    }

    /**
     * Validate that entries balance (total debit == total credit).
     *
     * @throws \Exception
     */
    private function validateEntries(array $entries): void
    {
        if (empty($entries)) {
            throw new \Exception('Tidak ada entri jurnal yang diberikan.');
        }

        $totalDebit  = array_sum(array_column($entries, 'debit'));
        $totalCredit = array_sum(array_column($entries, 'credit'));

        if (abs(round($totalDebit, 2) - round($totalCredit, 2)) > 0.001) {
            throw new \Exception(
                "Jurnal tidak balance. Total Debit: {$totalDebit} | Total Kredit: {$totalCredit}"
            );
        }
    }

    /**
     * Build a simple two-line entry (Dr/Cr pair).
     */
    public static function drCr(int $drCoaId, int $crCoaId, float $amount): array
    {
        return [
            ['coa_id' => $drCoaId, 'debit' => $amount, 'credit' => 0],
            ['coa_id' => $crCoaId, 'debit' => 0,       'credit' => $amount],
        ];
    }
}
