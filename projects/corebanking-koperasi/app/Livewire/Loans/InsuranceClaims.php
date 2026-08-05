<?php

namespace App\Livewire\Loans;

use App\Models\InsuranceClaim;
use App\Models\LoanAccount;
use App\Services\InsuranceOperationService;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class InsuranceClaims extends Component
{
    use WithPagination, LogsActivity, WithLogout;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 12;

    public $selectedLoanId = null;
    public ?string $incidentDate = null;
    public string $claimRemarks = '';
    public string $loanLookup = '';

    public $selectedClaimId = null;
    public $approvedAmount = null;
    public $paidAmount = null;

    public function mount(): void
    {
        $this->incidentDate = now()->toDateString();
        $this->logActivity('NAVIGATE', 'Klaim Asuransi Pinjaman');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function submitDeathClaim(InsuranceOperationService $service): void
    {
        $this->validate([
            'selectedLoanId' => 'required|exists:loan_accounts,id',
            'incidentDate' => 'required|date',
            'claimRemarks' => 'nullable|string|max:1000',
        ]);

        try {
            if (!$this->selectedLoanId && filled(trim($this->loanLookup))) {
                $this->resolveSelectedLoanFromLookup();
            }

            $loan = LoanAccount::findOrFail($this->selectedLoanId);
            $service->submitDeathClaim($loan, $this->incidentDate, $this->claimRemarks ?: null);

            $this->reset(['selectedLoanId', 'claimRemarks', 'loanLookup']);
            $this->incidentDate = now()->toDateString();
            session()->flash('success', 'Klaim meninggal dunia berhasil diajukan.');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function updatedLoanLookup($value): void
    {
        $this->selectedLoanId = null;
        $lookup = trim((string) $value);

        if ($lookup === '') {
            return;
        }

        $accountNo = trim(strtok($lookup, '-'));
        if ($accountNo === '') {
            return;
        }

        $loan = LoanAccount::where('account_no', $accountNo)->first();
        if ($loan) {
            $this->selectedLoanId = $loan->id;
        }
    }

    public function selectClaimForApproval(int $claimId): void
    {
        $claim = InsuranceClaim::findOrFail($claimId);
        $this->selectedClaimId = $claimId;
        $this->approvedAmount = round((float) $claim->claim_amount, 2);
    }

    public function approveClaim(InsuranceOperationService $service): void
    {
        $this->validate([
            'selectedClaimId' => 'required|exists:insurance_claims,id',
            'approvedAmount' => 'required|numeric|min:1',
        ]);

        try {
            $claim = InsuranceClaim::findOrFail($this->selectedClaimId);
            $service->approveClaimAndRecognizeSettlement($claim, round((float) $this->approvedAmount, 2));

            $this->reset(['selectedClaimId', 'approvedAmount']);
            session()->flash('success', 'Klaim berhasil di-approve dan jurnal pengakuan terbentuk.');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function selectClaimForPayment(int $claimId): void
    {
        $claim = InsuranceClaim::findOrFail($claimId);
        $this->selectedClaimId = $claimId;
        $remaining = max(0, (float) $claim->approved_amount - (float) $claim->paid_amount);
        $this->paidAmount = round($remaining, 2);
    }

    public function recordPayment(InsuranceOperationService $service): void
    {
        $this->validate([
            'selectedClaimId' => 'required|exists:insurance_claims,id',
            'paidAmount' => 'required|numeric|min:1',
        ]);

        try {
            $claim = InsuranceClaim::findOrFail($this->selectedClaimId);
            $service->recordClaimPayment($claim, round((float) $this->paidAmount, 2));

            $this->reset(['selectedClaimId', 'paidAmount']);
            session()->flash('success', 'Pembayaran klaim berhasil dicatat dan jurnal kas terbentuk.');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $search = trim($this->search);
        $claimQuery = InsuranceClaim::with([
            'loanAccount.cif',
            'policy.insuranceProduct.provider',
        ])->latest('id');

        if ($search !== '') {
            $claimQuery->where(function ($q) use ($search) {
                $q->where('claim_no', 'like', "%{$search}%")
                    ->orWhereHas('loanAccount', function ($q2) use ($search) {
                        $q2->where('account_no', 'like', "%{$search}%")
                            ->orWhereHas('cif', function ($q3) use ($search) {
                                $q3->where('name', 'like', "%{$search}%")
                                    ->orWhere('cif_no', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($this->statusFilter) {
            $claimQuery->where('status', $this->statusFilter);
        }

        $loansEligible = LoanAccount::with(['cif', 'insurancePolicies.insuranceProduct', 'product', 'insuranceProduct'])
            ->whereIn('status', ['ACTIVE', 'NPL'])
            ->where(function ($q) {
                $q->whereHas('insurancePolicies', function ($qPolicy) {
                    $qPolicy->whereIn('status', ['ACTIVE', 'SUBMITTED']);
                })->orWhereHas('insuranceProduct');
            })
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $claims = ($search !== '' || filled($this->statusFilter))
            ? $claimQuery->paginate($this->perPage)
            : InsuranceClaim::whereRaw('1 = 0')->paginate($this->perPage);

        return view('livewire.loans.insurance-claims', [
            'claims' => $claims,
            'loansEligible' => $loansEligible,
        ]);
    }

    private function resolveSelectedLoanFromLookup(): void
    {
        $lookup = trim($this->loanLookup);
        if ($lookup === '') {
            return;
        }

        $accountNo = trim(strtok($lookup, '-'));
        if ($accountNo === '') {
            return;
        }

        $loan = LoanAccount::where('account_no', $accountNo)->first();
        if ($loan) {
            $this->selectedLoanId = $loan->id;
        }
    }
}
