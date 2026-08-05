<?php

namespace App\Services;

use App\Models\InsuranceClaim;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\LoanAccount;
use App\Models\LoanInsurancePolicy;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InsuranceOperationService
{
    public function createPolicyForLoan(LoanAccount $loan): ?LoanInsurancePolicy
    {
        $insuranceProduct = $loan->insuranceProduct;

        if (!$insuranceProduct || (float) $loan->insurance_fee <= 0) {
            return null;
        }

        $existing = LoanInsurancePolicy::where('loan_account_id', $loan->id)
            ->where('insurance_product_id', $insuranceProduct->id)
            ->whereIn('status', ['ACTIVE', 'SUBMITTED'])
            ->first();

        if ($existing) {
            return $existing;
        }

        $startDate = $loan->disbursement_date ? Carbon::parse($loan->disbursement_date) : now();
        $endDate = $insuranceProduct->coverage_period_months
            ? $startDate->copy()->addMonths((int) $insuranceProduct->coverage_period_months)
            : null;

        return LoanInsurancePolicy::create([
            'loan_account_id' => $loan->id,
            'insurance_product_id' => $insuranceProduct->id,
            'policy_no' => null,
            'certificate_no' => null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'coverage_amount' => $loan->principal_amount,
            'premium_amount' => $loan->insurance_fee,
            'status' => 'ACTIVE',
            'notes' => 'Auto-created from loan disbursement.',
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
    }

    public function submitClaim(array $data): InsuranceClaim
    {
        return DB::transaction(function () use ($data) {
            $policy = LoanInsurancePolicy::findOrFail($data['loan_insurance_policy_id']);

            return InsuranceClaim::create([
                'claim_no' => $this->generateClaimNo(),
                'loan_account_id' => $policy->loan_account_id,
                'loan_insurance_policy_id' => $policy->id,
                'incident_date' => $data['incident_date'] ?? null,
                'submission_date' => $data['submission_date'] ?? now()->toDateString(),
                'claim_amount' => $data['claim_amount'],
                'approved_amount' => 0,
                'paid_amount' => 0,
                'status' => 'SUBMITTED',
                'remarks' => $data['remarks'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        });
    }

    public function submitDeathClaim(LoanAccount $loan, ?string $incidentDate = null, ?string $remarks = null): InsuranceClaim
    {
        return DB::transaction(function () use ($loan, $incidentDate, $remarks) {
            if (!in_array($loan->status, ['ACTIVE', 'NPL'])) {
                throw new Exception('Klaim meninggal hanya dapat diajukan untuk pinjaman ACTIVE/NPL.');
            }

            $policy = $loan->insurancePolicies()
                ->where('status', 'ACTIVE')
                ->latest('id')
                ->first();

            if (!$policy) {
                // Fallback: attempt to auto-create policy for legacy loans that missed policy generation.
                $policy = $this->createPolicyForLoan($loan);
            }

            if (!$policy || $policy->status !== 'ACTIVE') {
                throw new Exception('Polis asuransi aktif tidak ditemukan untuk pinjaman ini. Pastikan produk pinjaman memiliki mapping produk asuransi.');
            }

            $alreadyOpenClaim = InsuranceClaim::where('loan_account_id', $loan->id)
                ->whereIn('status', ['SUBMITTED', 'APPROVED'])
                ->exists();

            if ($alreadyOpenClaim) {
                throw new Exception('Masih ada klaim asuransi yang belum selesai untuk pinjaman ini.');
            }

            $claimAmount = (float) $loan->outstanding_principal + (float) $loan->outstanding_interest + (float) $loan->outstanding_penalty;

            $claim = InsuranceClaim::create([
                'claim_no' => $this->generateClaimNo(),
                'loan_account_id' => $loan->id,
                'loan_insurance_policy_id' => $policy->id,
                'incident_date' => $incidentDate ? Carbon::parse($incidentDate)->toDateString() : now()->toDateString(),
                'submission_date' => now()->toDateString(),
                'claim_amount' => $claimAmount,
                'approved_amount' => 0,
                'paid_amount' => 0,
                'status' => 'SUBMITTED',
                'remarks' => $remarks ?: 'Pengajuan klaim meninggal dunia.',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $loan->status = 'CLAIM_SUBMITTED';
            $loan->updated_by = Auth::id();
            $loan->save();

            return $claim;
        });
    }

    public function approveClaimAndRecognizeSettlement(InsuranceClaim $claim, float $approvedAmount): InsuranceClaim
    {
        return DB::transaction(function () use ($claim, $approvedAmount) {
            $claim->loadMissing(['loanAccount.product', 'policy.insuranceProduct']);
            $loan = $claim->loanAccount;
            $insuranceProduct = $claim->policy->insuranceProduct;

            if ($claim->status !== 'SUBMITTED') {
                throw new Exception('Hanya klaim SUBMITTED yang dapat di-approve.');
            }

            if ($approvedAmount <= 0) {
                throw new Exception('Nominal approved klaim harus lebih dari 0.');
            }

            if (!$insuranceProduct?->claim_receivable_coa_id) {
                throw new Exception('COA piutang klaim asuransi belum diatur di produk asuransi.');
            }

            $principal = (float) $loan->outstanding_principal;
            $interest = (float) $loan->outstanding_interest;
            $penalty = (float) $loan->outstanding_penalty;
            $outstanding = $principal + $interest + $penalty;
            $settlementAmount = min($approvedAmount, $outstanding);

            if ($settlementAmount <= 0) {
                throw new Exception('Tidak ada outstanding yang dapat diselesaikan.');
            }

            $journal = Journal::create([
                'branch_id' => $loan->branch_id,
                'reference_no' => $this->generateInsuranceRef('ICR'),
                'transaction_date' => now(),
                'description' => "Pengakuan Klaim Asuransi {$claim->claim_no} - {$loan->account_no}",
                'status' => 'APPROVED',
                'created_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id' => $insuranceProduct->claim_receivable_coa_id,
                'debit' => $settlementAmount,
                'credit' => 0,
            ]);

            $remaining = $settlementAmount;

            $principalSettled = min($principal, $remaining);
            if ($principalSettled > 0) {
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $loan->product->principal_coa_id,
                    'debit' => 0,
                    'credit' => $principalSettled,
                ]);
                $remaining -= $principalSettled;
            }

            $interestSettled = min($interest, $remaining);
            if ($interestSettled > 0) {
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $loan->product->interest_revenue_coa_id,
                    'debit' => 0,
                    'credit' => $interestSettled,
                ]);
                $remaining -= $interestSettled;
            }

            $penaltySettled = min($penalty, $remaining);
            if ($penaltySettled > 0 && $loan->product->penalty_revenue_coa_id) {
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $loan->product->penalty_revenue_coa_id,
                    'debit' => 0,
                    'credit' => $penaltySettled,
                ]);
                $remaining -= $penaltySettled;
            }

            $loan->outstanding_principal = max(0, $principal - $principalSettled);
            $loan->outstanding_interest = max(0, $interest - $interestSettled);
            $loan->outstanding_penalty = max(0, $penalty - $penaltySettled);
            $loan->status = $loan->outstandingTotal <= 0 ? 'CLOSED' : 'CLAIM_APPROVED';
            $loan->updated_by = Auth::id();
            $loan->save();

            if ($loan->status === 'CLOSED') {
                $loan->schedules()
                    ->whereIn('status', ['UNPAID', 'PARTIAL'])
                    ->update(['status' => 'PAID']);
            }

            $claim->approved_amount = $settlementAmount;
            $claim->approval_date = now()->toDateString();
            $claim->status = 'APPROVED';
            $claim->recognition_journal_id = $journal->id;
            $claim->updated_by = Auth::id();
            $claim->save();

            return $claim;
        });
    }

    public function recordClaimPayment(InsuranceClaim $claim, float $paidAmount): InsuranceClaim
    {
        return DB::transaction(function () use ($claim, $paidAmount) {
            $claim->loadMissing(['loanAccount.product', 'policy.insuranceProduct']);
            $loan = $claim->loanAccount;
            $insuranceProduct = $claim->policy->insuranceProduct;

            if (!in_array($claim->status, ['APPROVED', 'PARTIALLY_PAID'])) {
                throw new Exception('Pembayaran klaim hanya bisa untuk klaim APPROVED/PARTIALLY_PAID.');
            }

            if ($paidAmount <= 0) {
                throw new Exception('Nominal pembayaran klaim harus lebih dari 0.');
            }

            if (!$insuranceProduct?->claim_receivable_coa_id || !$loan->product->default_cash_coa_id) {
                throw new Exception('Mapping COA klaim atau kas/bank belum lengkap.');
            }

            $remaining = (float) $claim->approved_amount - (float) $claim->paid_amount;
            $paymentAmount = min($paidAmount, $remaining);
            if ($paymentAmount <= 0) {
                throw new Exception('Tidak ada sisa approved claim untuk dibayarkan.');
            }

            $journal = Journal::create([
                'branch_id' => $loan->branch_id,
                'reference_no' => $this->generateInsuranceRef('ICP'),
                'transaction_date' => now(),
                'description' => "Pembayaran Klaim Asuransi {$claim->claim_no} - {$loan->account_no}",
                'status' => 'APPROVED',
                'created_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id' => $loan->product->default_cash_coa_id,
                'debit' => $paymentAmount,
                'credit' => 0,
            ]);

            JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id' => $insuranceProduct->claim_receivable_coa_id,
                'debit' => 0,
                'credit' => $paymentAmount,
            ]);

            $claim->paid_amount = (float) $claim->paid_amount + $paymentAmount;
            $claim->payment_date = now()->toDateString();
            $claim->payment_journal_id = $journal->id;
            $claim->status = ((float) $claim->paid_amount >= (float) $claim->approved_amount) ? 'PAID' : 'PARTIALLY_PAID';
            $claim->updated_by = Auth::id();
            $claim->save();

            if ($loan->status === 'CLAIM_APPROVED' && $claim->status === 'PAID' && $loan->outstandingTotal <= 0) {
                $loan->status = 'CLOSED';
                $loan->updated_by = Auth::id();
                $loan->save();
            }

            return $claim;
        });
    }

    private function generateClaimNo(): string
    {
        $date = now()->format('Ymd');
        $latest = InsuranceClaim::where('claim_no', 'like', "CLM-{$date}-%")
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($latest) {
            $parts = explode('-', $latest->claim_no);
            $seq = ((int) end($parts)) + 1;
        }

        return sprintf('CLM-%s-%04d', $date, $seq);
    }

    private function generateInsuranceRef(string $prefix): string
    {
        return sprintf('%s-%s-%02d', $prefix, now()->format('YmdHis'), random_int(10, 99));
    }
}
