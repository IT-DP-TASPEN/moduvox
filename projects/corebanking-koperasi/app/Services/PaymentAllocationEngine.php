<?php

namespace App\Services;

use App\Models\LoanAccount;

/**
 * PaymentAllocationEngine
 *
 * Implements OJK payment allocation priority rules:
 *
 * KOL 1–2 (Lancar / DPK):
 *   Priority: Bunga → Pokok → Denda
 *
 * KOL 3–5 (Kurang Lancar / Diragukan / Macet):
 *   Priority: Pokok → Bunga → Denda
 *
 * Allocation is applied per installment schedule, oldest first.
 */
class PaymentAllocationEngine
{
    /**
     * Determine the allocation priority order based on KOL level.
     *
     * @param  int $kolLevel  1–5
     * @return array  e.g. ['interest', 'principal', 'penalty']
     */
    public function getPriorityOrder(int $kolLevel): array
    {
        // OJK regulation: KOL 3-5 → principal first (recover principal of NPL loan)
        return $kolLevel >= 3
            ? ['principal', 'interest', 'penalty']
            : ['interest', 'principal', 'penalty'];
    }

    /**
     * Allocate a payment amount across unpaid schedules.
     *
     * @param  LoanAccount $loan
     * @param  float       $paymentAmount
     * @return AllocationResult
     */
    public function allocate(LoanAccount $loan, float $paymentAmount, bool $includeUpcoming = false): AllocationResult
    {
        $result    = new AllocationResult();
        $remaining = $paymentAmount;

        $prioritySteps = $this->getPriorityOrder($loan->kol_level ?? 1);

        // Manual repayment may pay ahead; auto-debit keeps due-date discipline.
        $schedules = $loan->schedules()
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->when(!$includeUpcoming, fn ($query) => $query->whereDate('due_date', '<=', now()->toDateString()))
            ->orderBy('due_date', 'asc')
            ->get();

        foreach ($schedules as $sched) {
            if ($remaining <= 0) break;

            foreach ($prioritySteps as $step) {
                if ($remaining <= 0) break;

                switch ($step) {
                    case 'interest':
                        $unpaid  = (float)$sched->interest_amount - (float)$sched->interest_paid;
                        if ($unpaid > 0) {
                            $apply = min($unpaid, $remaining);
                            $sched->interest_paid += $apply;
                            $remaining            -= $apply;
                            $result->totalInterestPaid += $apply;
                        }
                        break;

                    case 'principal':
                        $unpaid  = (float)$sched->principal_amount - (float)$sched->principal_paid;
                        if ($unpaid > 0) {
                            $apply = min($unpaid, $remaining);
                            $sched->principal_paid += $apply;
                            $remaining             -= $apply;
                            $result->totalPrincipalPaid += $apply;
                        }
                        break;

                    case 'penalty':
                        $unpaid  = (float)($sched->penalty_amount ?? 0) - (float)($sched->penalty_paid ?? 0);
                        if ($unpaid > 0) {
                            $apply = min($unpaid, $remaining);
                            $sched->penalty_paid += $apply;
                            $remaining           -= $apply;
                            $result->totalPenaltyPaid += $apply;
                        }
                        break;
                }
            }

            // Update schedule status
            $totalInvoiced = (float)$sched->principal_amount
                           + (float)$sched->interest_amount
                           + (float)($sched->penalty_amount ?? 0);

            $totalPaid = (float)$sched->principal_paid
                       + (float)$sched->interest_paid
                       + (float)($sched->penalty_paid ?? 0);

            $sched->status = (round($totalPaid, 2) >= round($totalInvoiced, 2))
                ? 'PAID'
                : 'PARTIAL';

            $sched->save();
            $result->processedSchedules[] = $sched->id;
        }

        $result->appliedAmount = $result->totalPrincipalPaid
                               + $result->totalInterestPaid
                               + $result->totalPenaltyPaid;

        $result->remainingAmount = $remaining;

        return $result;
    }

    /**
     * Calculate OJK-standard KOL level based on Days Past Due.
     *
     * OJK POJK No.40/POJK.03/2019 — BPR Collectibility:
     *   KOL 1 (Lancar)           : DPD = 0
     *   KOL 2 (DPK)              : 1  ≤ DPD ≤ 90
     *   KOL 3 (Kurang Lancar)    : 91 ≤ DPD ≤ 120
     *   KOL 4 (Diragukan)        : 121 ≤ DPD ≤ 180
     *   KOL 5 (Macet)            : DPD > 180
     *
     * @param  int $dpd  Days Past Due
     * @return int  KOL level 1–5
     */
    public function calculateKol(int $dpd): int
    {
        return match (true) {
            $dpd === 0        => 1,
            $dpd <= 90        => 2,
            $dpd <= 120       => 3,
            $dpd <= 180       => 4,
            default           => 5,
        };
    }

    /**
     * Get human-readable KOL status label.
     */
    public function getKolLabel(int $kol): string
    {
        return match ($kol) {
            1 => 'Lancar',
            2 => 'DPK (Dalam Perhatian Khusus)',
            3 => 'Kurang Lancar',
            4 => 'Diragukan',
            5 => 'Macet',
            default => 'Unknown',
        };
    }
}

/**
 * Value object returned by PaymentAllocationEngine::allocate()
 */
class AllocationResult
{
    public float $totalPrincipalPaid = 0;
    public float $totalInterestPaid  = 0;
    public float $totalPenaltyPaid   = 0;
    public float $appliedAmount      = 0;
    public float $remainingAmount    = 0;
    public array $processedSchedules = [];

    public function hasOverpayment(): bool
    {
        return $this->remainingAmount > 0;
    }

    public function toArray(): array
    {
        return [
            'total_principal_paid'  => $this->totalPrincipalPaid,
            'total_interest_paid'   => $this->totalInterestPaid,
            'total_penalty_paid'    => $this->totalPenaltyPaid,
            'applied_amount'        => $this->appliedAmount,
            'remaining_amount'      => $this->remainingAmount,
            'processed_schedules'   => $this->processedSchedules,
        ];
    }
}
