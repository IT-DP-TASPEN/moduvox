<?php

namespace App\Livewire\Deposits;

use App\Models\DepositAccount;
use App\Models\Branch;
use App\Services\DepositOperationService;
use App\Services\SettlementEngine;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Livewire\Component;
use Livewire\WithPagination;

class Withdrawal extends Component
{
    use WithPagination, ApprovesActions, LogsActivity, WithLogout;

    public $search = '';
    public $filter_branch = '';
    public $viewMode = 'grid'; // grid | form
    public $selectedAccountId = null;
    public $account = null;

    public $penalty_amount = 0;
    public $payout_channel = 'CASH'; // CASH | ABA | INTERNAL
    public $bank_coa_id = null;
    public $cash_coa_id = null;
    public $target_saving_account_id = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter_branch' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
        'selectedAccountId' => ['except' => null]
    ];

    public function mount()
    {
        if ($this->selectedAccountId) {
            $this->selectAccount($this->selectedAccountId);
        }
        $this->logActivity('NAVIGATE', 'Pencairan Simpanan Berjangka');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPayoutChannel($channel)
    {
        $this->bank_coa_id = null;
        $this->cash_coa_id = null;

        $options = SettlementEngine::getSelectableCoas($channel);
        if ($options->count() === 1) {
            if ($channel === 'ABA') {
                $this->bank_coa_id = $options->first()->id;
            } elseif ($channel === 'CASH') {
                $this->cash_coa_id = $options->first()->id;
            }
        }
    }

    public function selectAccount($id)
    {
        $this->account = DepositAccount::with(['cif', 'product', 'savingAccount'])->find($id);
        if ($this->account) {
            $this->selectedAccountId = $id;
            $this->target_saving_account_id = $this->account->saving_account_id;
            $this->payout_channel = $this->target_saving_account_id ? 'INTERNAL' : 'CASH';
            $this->viewMode = 'form';
            $this->calculatePenalty();
        }
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedAccountId = null;
        $this->account = null;
        $this->target_saving_account_id = null;
        $this->payout_channel = 'CASH';
        $this->bank_coa_id = null;
        $this->cash_coa_id = null;
    }

    public function calculatePenalty()
    {
        if (!$this->account) return;

        // Simple logic: 1% of principal if not matured
        if ($this->account->maturity_date->isFuture()) {
            $this->penalty_amount = $this->account->amount * 0.01;
        } else {
            $this->penalty_amount = 0;
        }
    }

    public function submit(DepositOperationService $service)
    {
        $rules = [
            'selectedAccountId' => 'required|exists:deposit_accounts,id',
            'penalty_amount' => 'required|numeric|min:0',
            'payout_channel' => 'required|in:CASH,ABA,INTERNAL',
            'bank_coa_id' => 'nullable|exists:coas,id',
            'cash_coa_id' => 'nullable|exists:coas,id',
        ];

        // If INTERNAL channel, require target savings account
        if ($this->payout_channel === 'INTERNAL') {
            $rules['target_saving_account_id'] = 'required|exists:saving_accounts,id';
        }

        $this->validate($rules);

        // Verify CIF match if INTERNAL
        if ($this->payout_channel === 'INTERNAL' && $this->target_saving_account_id) {
            $targetAccount = \App\Models\SavingAccount::findOrFail($this->target_saving_account_id);
            if ($targetAccount->cif_id !== $this->account->cif_id) {
                $this->addError('target_saving_account_id', 'Rekening tabungan harus milik nasabah yang sama.');
                return;
            }
        }

        $data = [
            'deposit_account_id' => $this->selectedAccountId,
            'penalty_amount' => (float)$this->penalty_amount,
            'payout_channel' => $this->payout_channel,
            'coa_override_id' => $this->payout_channel === 'ABA'
                ? ($this->bank_coa_id ?: null)
                : ($this->cash_coa_id ?: null),
        ];

        // Add target account for INTERNAL transfer
        if ($this->payout_channel === 'INTERNAL') {
            $data['saving_account_id'] = $this->target_saving_account_id;
        }

        // Approval check
        $status = $this->interceptAction('deposits.withdrawal', 'CLOSE', $data, $this->selectedAccountId);

        $this->logActivity('WITHDRAW_DEPOSIT_REQUEST', "Mengajukan pencairan simpanan berjangka [{$this->account->account_no}] untuk Anggota [{$this->account->cif->name}]");

        if ($status === 'PENDING') {
            session()->flash('success', 'Permohonan pencairan simpanan berjangka telah diajukan ke antrean persetujuan.');
        } else {
            $service->closeAccount($data);
            session()->flash('success', 'Simpanan Berjangka berhasil dicairkan.');
        }

        return redirect()->route('deposits.inquiry');
    }

    public function render()
    {
        $query = DepositAccount::with(['cif', 'product', 'branch', 'bilyet'])
            ->whereIn('status', ['ACTIVE', 'MATURED']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('account_no', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cif', function ($qc) {
                        $qc->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('cif_no', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filter_branch) {
            $query->where('branch_id', $this->filter_branch);
        }

        if ($this->search || $this->filter_branch) {
            $items = $query->orderBy('maturity_date', 'asc')->paginate(10);
        } else {
            $items = DepositAccount::whereRaw('1 = 0')->paginate(10);
        }

        return view('livewire.deposits.withdrawal', [
            'items' => $items,
            'branches' => Branch::where('is_active', true)->get(),
            'abaCoas' => SettlementEngine::getSelectableCoas('ABA'),
            'cashCoas' => SettlementEngine::getSelectableCoas('CASH'),
        ])->layout('layouts.app');
    }
}
