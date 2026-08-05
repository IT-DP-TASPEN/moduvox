<?php

namespace App\Livewire\Loans;

use App\Models\InsuranceClaim;
use App\Models\LoanAccount;
use App\Services\LoanOperationService;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Settlement extends Component
{
    use WithPagination, ApprovesActions, LogsActivity;

    public string $search = '';
    public string $viewMode = 'grid';
    public int $perPage = 12;
    public ?LoanAccount $selectedAccount = null;
    public $payment_amount = null;
    public string $payment_amount_display = '';
    public $principal_amount = null;
    public string $principal_amount_display = '';
    public $interest_obligation_amount = 0;
    public string $interest_obligation_display = '0';
    public $penalty_amount = 0;
    public string $penalty_amount_display = '0';
    public bool $hasOpenInsuranceClaim = false;
    public bool $isInsuranceLoan = false;
    public bool $showInsuranceConfirmationModal = false;
    public bool $insuranceConfirmationAccepted = false;

    public function mount(): void
    {
        $this->logActivity('NAVIGATE', 'Pelunasan Pinjaman');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function viewAccount(int $id): void
    {
        $this->selectedAccount = LoanAccount::with(['cif', 'product', 'savingAccount', 'insuranceClaims', 'insurancePolicies'])->findOrFail($id);
        $this->principal_amount = (float) $this->selectedAccount->outstanding_principal;
        $this->interest_obligation_amount = (float) $this->selectedAccount->outstanding_interest;
        $this->penalty_amount = (float) $this->selectedAccount->outstanding_penalty;
        $this->principal_amount_display = number_format((float) $this->principal_amount, 2, ',', '.');
        $this->interest_obligation_display = $this->formatMoneyValue((float) $this->interest_obligation_amount);
        $this->penalty_amount_display = $this->formatMoneyValue((float) $this->penalty_amount);
        $this->payment_amount = $this->calculateSettlementTotal();
        $this->payment_amount_display = number_format((float) $this->payment_amount, 2, ',', '.');
        $this->hasOpenInsuranceClaim = $this->selectedAccount->insuranceClaims
            ->whereIn('status', ['SUBMITTED', 'APPROVED', 'PARTIALLY_PAID'])
            ->isNotEmpty();
        $this->isInsuranceLoan = !is_null($this->selectedAccount->insurance_product_id)
            || $this->selectedAccount->insurancePolicies->isNotEmpty();
        $this->viewMode = 'detail';
    }

    public function closeView(): void
    {
        $this->viewMode = 'grid';
        $this->selectedAccount = null;
        $this->payment_amount = null;
        $this->payment_amount_display = '';
        $this->principal_amount = null;
        $this->principal_amount_display = '';
        $this->interest_obligation_amount = 0;
        $this->interest_obligation_display = '0';
        $this->penalty_amount = 0;
        $this->penalty_amount_display = '0';
        $this->hasOpenInsuranceClaim = false;
        $this->isInsuranceLoan = false;
        $this->showInsuranceConfirmationModal = false;
        $this->insuranceConfirmationAccepted = false;
    }

    public function updatedPaymentAmountDisplay($value): void
    {
        $amount = $this->parseMoney($value);

        $this->payment_amount = $amount;
        $this->payment_amount_display = $value !== '' ? number_format($amount, 2, ',', '.') : '';
    }

    public function updatedInterestObligationDisplay($value): void
    {
        $this->interest_obligation_amount = $this->parseMoney($value);
        $this->interest_obligation_display = $value !== '' ? $this->formatMoneyInput($value) : '';
        $this->syncSettlementTotal();
    }

    public function updatedPenaltyAmountDisplay($value): void
    {
        $this->penalty_amount = $this->parseMoney($value);
        $this->penalty_amount_display = $value !== '' ? $this->formatMoneyInput($value) : '';
        $this->syncSettlementTotal();
    }

    public function formatInterestObligation(): void
    {
        $this->interest_obligation_amount = $this->parseMoney($this->interest_obligation_display);
        $this->interest_obligation_display = $this->formatMoneyValue(
            (float) $this->interest_obligation_amount,
            str_contains($this->interest_obligation_display, ',')
        );
    }

    public function formatPenaltyAmount(): void
    {
        $this->penalty_amount = $this->parseMoney($this->penalty_amount_display);
        $this->penalty_amount_display = $this->formatMoneyValue(
            (float) $this->penalty_amount,
            str_contains($this->penalty_amount_display, ',')
        );
    }

    private function parseMoney($value): float
    {
        $normalized = str_replace('.', '', (string) $value);
        $normalized = str_replace(',', '.', $normalized);
        $normalized = preg_replace('/[^0-9.]/', '', $normalized);

        return $normalized !== '' ? round((float) $normalized, 2) : 0;
    }

    private function formatMoneyInput($value): string
    {
        $raw = preg_replace('/[^0-9,]/', '', (string) $value);
        [$whole, $decimal] = array_pad(explode(',', $raw, 2), 2, null);
        $whole = ltrim($whole, '0') ?: '0';
        $formattedWhole = number_format((float) $whole, 0, ',', '.');

        if ($decimal === null) {
            return $formattedWhole;
        }

        return $formattedWhole . ',' . substr($decimal, 0, 2);
    }

    private function formatMoneyValue(float $value, bool $withDecimal = false): string
    {
        return number_format($value, $withDecimal ? 2 : 0, ',', '.');
    }

    private function calculateSettlementTotal(): float
    {
        return round((float) $this->principal_amount + (float) $this->interest_obligation_amount + (float) $this->penalty_amount, 2);
    }

    private function syncSettlementTotal(): void
    {
        $this->payment_amount = $this->calculateSettlementTotal();
        $this->payment_amount_display = number_format((float) $this->payment_amount, 2, ',', '.');
    }

    public function requestSettlement(): void
    {
        if ($this->hasOpenInsuranceClaim) {
            session()->flash('error', 'Pinjaman sedang dalam proses klaim asuransi. Selesaikan melalui menu Klaim Asuransi.');
            return;
        }

        if ($this->isInsuranceLoan) {
            $this->showInsuranceConfirmationModal = true;
            return;
        }

        $this->processSettlement();
    }

    public function closeInsuranceConfirmationModal(): void
    {
        $this->showInsuranceConfirmationModal = false;
        $this->insuranceConfirmationAccepted = false;
    }

    public function confirmInsuranceAndProcessSettlement(): void
    {
        $this->insuranceConfirmationAccepted = true;
        $this->showInsuranceConfirmationModal = false;
        $this->processSettlement();
    }

    public function processSettlement(): void
    {
        $service = app(LoanOperationService::class);
        $interestHadDecimal = str_contains($this->interest_obligation_display, ',');
        $penaltyHadDecimal = str_contains($this->penalty_amount_display, ',');
        $this->interest_obligation_amount = $this->parseMoney($this->interest_obligation_display);
        $this->penalty_amount = $this->parseMoney($this->penalty_amount_display);
        $this->interest_obligation_display = $this->formatMoneyValue((float) $this->interest_obligation_amount, $interestHadDecimal);
        $this->penalty_amount_display = $this->formatMoneyValue((float) $this->penalty_amount, $penaltyHadDecimal);
        $this->syncSettlementTotal();

        $this->validate([
            'payment_amount' => 'required|numeric|min:1',
            'principal_amount' => 'required|numeric|min:0',
            'interest_obligation_amount' => 'required|numeric|min:0',
            'penalty_amount' => 'required|numeric|min:0',
        ]);

        $loan = $this->selectedAccount;
        if (!$loan || $loan->status !== 'ACTIVE') {
            session()->flash('error', 'Hanya pinjaman ACTIVE yang bisa dilunasi.');
            return;
        }

        $hasOpenClaim = InsuranceClaim::where('loan_account_id', $loan->id)
            ->whereIn('status', ['SUBMITTED', 'APPROVED', 'PARTIALLY_PAID'])
            ->exists();

        if ($hasOpenClaim) {
            session()->flash('error', 'Pinjaman sedang dalam proses klaim asuransi. Selesaikan melalui menu Klaim Asuransi.');
            return;
        }

        if ($this->isInsuranceLoan && !$this->insuranceConfirmationAccepted) {
            $this->showInsuranceConfirmationModal = true;
            return;
        }

        $payment = round((float) $this->payment_amount, 2);
        $principal = round((float) $this->principal_amount, 2);
        $interest = round((float) $this->interest_obligation_amount, 2);
        $penalty = round((float) $this->penalty_amount, 2);
        $expectedPayment = round($principal + $interest + $penalty, 2);

        if ($principal !== round((float) $loan->outstanding_principal, 2)) {
            session()->flash('error', 'Pokok pelunasan harus sama dengan outstanding pokok saat ini.');
            return;
        }

        if ($payment !== $expectedPayment) {
            session()->flash('error', 'Total pelunasan harus sama dengan pokok + kewajiban bunga + denda.');
            return;
        }

        $data = [
            'amount' => $payment,
            'principal_amount' => $principal,
            'interest_amount' => $interest,
            'penalty_amount' => $penalty,
            'loan_account_id' => $loan->id,
        ];

        $status = $this->interceptAction('loans.settlement', 'Settlement', $data, $loan->id);

        if ($status === 'PENDING') {
            session()->flash('success', 'Permintaan pelunasan diajukan ke checker.');
        } else {
            DB::transaction(function () use ($service, $loan, $payment) {
                $service->processSettlementFromSavings(
                    $loan,
                    $payment,
                    (float) $this->interest_obligation_amount,
                    (float) $this->penalty_amount
                );
            });
            session()->flash('success', 'Pelunasan kredit berhasil diproses.');
        }

        $this->insuranceConfirmationAccepted = false;
        $this->closeView();
    }

    public function render()
    {
        $search = trim($this->search);

        $query = LoanAccount::with(['cif', 'product'])
            ->where('status', 'ACTIVE')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('account_no', 'like', '%' . $search . '%')
                        ->orWhere('pk_number', 'like', '%' . $search . '%')
                        ->orWhereHas('cif', function ($qCif) use ($search) {
                            $qCif->where('name', 'like', '%' . $search . '%')
                                ->orWhere('cif_no', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('id');

        $loans = $search !== ''
            ? $query->paginate($this->perPage)
            : LoanAccount::whereRaw('1 = 0')->paginate($this->perPage);

        return view('livewire.loans.settlement', [
            'loans' => $loans,
        ]);
    }
}
