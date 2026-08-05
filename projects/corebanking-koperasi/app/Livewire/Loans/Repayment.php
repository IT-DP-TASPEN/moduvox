<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LoanAccount;
use App\Services\SettlementEngine;
use App\Services\LoanOperationService;
use App\Traits\ApprovesActions;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsActivity;

class Repayment extends Component
{
    use WithPagination;
    use ApprovesActions;
    use LogsActivity;

    public $search = '';
    public $statusFilter = '';
    public int $perPage = 12;
    public $viewMode = 'grid'; // grid or detail
    public $selectedAccount = null;
    
    // Repayment Form
    public $channel = 'INTERNAL'; // INTERNAL | CASH | ABA
    public $bank_coa_id = null;
    public $cash_coa_id = null;
    public $payment_amount;
    public string $payment_amount_display = '';

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Pembayaran Angsuran');
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedChannel($channel)
    {
        $this->bank_coa_id = null;
        $this->cash_coa_id = null;

        $options = SettlementEngine::getSelectableCoas($channel);
        if ($options->count() === 1) {
            if ($channel === 'ABA') {
                $this->bank_coa_id = $options->first()->id;
            } else if ($channel === 'CASH') {
                $this->cash_coa_id = $options->first()->id;
            }
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    protected function parseMoneyInput(string|float|int|null $value): float
    {
        $normalized = str_replace('.', '', (string) $value);
        $normalized = str_replace(',', '.', $normalized);
        $normalized = preg_replace('/[^0-9.]/', '', $normalized);
        return $normalized !== '' ? round((float) $normalized, 2) : 0.0;
    }

    public function repaymentSourceStatus(): array
    {
        $amount = $this->parseMoneyInput($this->payment_amount_display);
        $loan = $this->selectedAccount;

        if (!$loan) {
            return [];
        }

        if ($this->channel === 'INTERNAL') {
            $account = $loan->savingAccount;
            $balance = $account ? round((float) $account->effective_balance, 2) : 0.0;

            return [
                'label' => 'Saldo efektif tabungan',
                'account' => $account
                    ? "{$account->account_no} - {$account->product?->name}"
                    : 'Rekening tabungan belum terhubung',
                'balance' => $balance,
                'is_sufficient' => $account && $account->status === 'ACTIVE' && $balance >= $amount,
                'status' => $account && $account->status === 'ACTIVE'
                    ? ($balance >= $amount ? 'CUKUP' : 'TIDAK CUKUP')
                    : 'TIDAK VALID',
                'blocks_submission' => true,
            ];
        }

        if ($this->channel === 'ABA') {
            $coaId = $this->bank_coa_id ?: ($loan->product?->default_bank_coa_id ?? $loan->product?->aba_transit_coa_id);
            $coa = $coaId ? \App\Models\Coa::find($coaId) : null;
            $balance = $coa ? $this->coaBalance((int) $coa->id, (int) $loan->branch_id) : 0.0;

            return [
                'label' => 'Saldo COA ABA',
                'account' => $coa ? "{$coa->coa_code} - {$coa->name}" : 'COA ABA belum dipilih',
                'balance' => $balance,
                'is_sufficient' => $coa ? $balance >= $amount : false,
                'status' => $coa ? ($balance >= $amount ? 'CUKUP' : 'TIDAK CUKUP') : 'BELUM DIPILIH',
                'blocks_submission' => true,
            ];
        }

        return [];
    }

    private function coaBalance(int $coaId, int $branchId): float
    {
        $coaType = \App\Models\Coa::whereKey($coaId)->value('type');
        $totals = DB::table('journal_entries')
            ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
            ->where('journals.status', 'APPROVED')
            ->where('journals.branch_id', $branchId)
            ->where('journal_entries.coa_id', $coaId)
            ->selectRaw('COALESCE(SUM(journal_entries.debit),0) as debit, COALESCE(SUM(journal_entries.credit),0) as credit')
            ->first();

        $debit = (float) ($totals->debit ?? 0);
        $credit = (float) ($totals->credit ?? 0);

        return round(in_array($coaType, ['ASSET', 'EXPENSE'], true) ? $debit - $credit : $credit - $debit, 2);
    }

    public function viewAccount($id)
    {
        $this->selectedAccount = LoanAccount::with(['cif.branch', 'product', 'savingAccount.product', 'savingAccount.branch', 'branch', 'schedules' => function ($q) {
            $q->whereIn('status', ['UNPAID', 'PARTIAL'])->orderBy('due_date', 'asc');
        }])->findOrFail($id);

        $dueAmount = round((float) $this->selectedAccount->schedules
            ->where('due_date', '<=', now()->format('Y-m-d'))
            ->sum(function($sched) {
                return ($sched->principal_amount - $sched->principal_paid) +
                       ($sched->interest_amount - $sched->interest_paid) +
                       ($sched->penalty_amount - $sched->penalty_paid);
            }), 2);

        $nextSchedule = $this->selectedAccount->schedules->first();
        $this->payment_amount = $dueAmount > 0 ? $dueAmount : round((float) (
            ($nextSchedule?->principal_amount - $nextSchedule?->principal_paid) +
            ($nextSchedule?->interest_amount - $nextSchedule?->interest_paid) +
            ($nextSchedule?->penalty_amount - $nextSchedule?->penalty_paid)
        ), 2);
            
        $this->payment_amount_display = number_format((float) $this->payment_amount, 2, ',', '.');

        $this->viewMode = 'detail';
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedAccount = null;
        $this->payment_amount = null;
        $this->payment_amount_display = '';
        $this->channel = 'INTERNAL';
        $this->bank_coa_id = null;
        $this->cash_coa_id = null;
    }

    public function processRepayment(LoanOperationService $service)
    {
        $this->payment_amount = $this->parseMoneyInput($this->payment_amount_display);
        $this->payment_amount_display = number_format((float) $this->payment_amount, 2, ',', '.');
        
        $this->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'channel' => 'required|in:INTERNAL,CASH,ABA',
            'bank_coa_id' => 'nullable|exists:coas,id',
            'cash_coa_id' => 'nullable|exists:coas,id',
        ]);

        $loan = $this->selectedAccount;
        if (!$loan || !in_array($loan->status, ['ACTIVE', 'NPL'], true)) return;

        if ($this->payment_amount > $loan->outstandingTotal) {
            session()->flash('error', 'Nominal bayar melebihi total seluruh sisa hutang.');
            return;
        }

        $coaOverrideId = ($this->channel === 'ABA') ? ($this->bank_coa_id ?: null) : ($this->cash_coa_id ?: null);
        $sourceStatus = $this->repaymentSourceStatus();

        if (($sourceStatus['blocks_submission'] ?? false) && !($sourceStatus['is_sufficient'] ?? false)) {
            $this->addError('payment_amount', 'Saldo rekening sumber tidak mencukupi untuk pembayaran ini.');
            return;
        }

        $data = [
            'amount' => $this->payment_amount,
            'loan_account_id' => $loan->id,
            'channel' => $this->channel,
            'coa_override_id' => $coaOverrideId,
            'source_account' => $sourceStatus['account'] ?? '-',
            'source_balance' => $sourceStatus['balance'] ?? null,
            'source_balance_status' => $sourceStatus['status'] ?? null,
        ];

        $status = $this->interceptAction('loans.repayment', 'Repayment', $data, $loan->id);

        if ($status === 'PENDING') {
            session()->flash('success', 'Permintaan pembayaran diajukan ke checker. Mutasi terbentuk setelah approval.');
        } else {
            DB::transaction(function() use ($service, $loan, $coaOverrideId) {
                if ($this->channel === 'INTERNAL') {
                    $service->processManualRepaymentFromSavings($loan, $this->payment_amount);
                } else {
                    $service->processRepayment(
                        $loan,
                        $this->payment_amount,
                        'REPAYMENT_MANUAL',
                        $this->channel,
                        $coaOverrideId
                    );
                }
            });
            session()->flash('success', 'Pembayaran angsuran manual berhasil diproses.');
        }

        $this->closeView();
    }

    public function render()
    {
        $search = trim((string) $this->search);

        $query = LoanAccount::with(['cif', 'product', 'schedules'])
            ->whereIn('status', ['ACTIVE', 'NPL'])
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
            ->orderBy('id', 'desc');

        if (filled($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        $loans = ($search !== '' || filled($this->statusFilter))
            ? $query->paginate($this->perPage)
            : LoanAccount::whereRaw('1 = 0')->paginate($this->perPage);

        $abaCoas  = SettlementEngine::getSelectableCoas('ABA');
        $cashCoas = SettlementEngine::getSelectableCoas('CASH');

        return view('livewire.loans.repayment', [
            'loans' => $loans,
            'abaCoas' => $abaCoas,
            'cashCoas' => $cashCoas,
        ]);
    }
}
