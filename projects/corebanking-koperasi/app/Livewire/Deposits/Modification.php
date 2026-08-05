<?php

namespace App\Livewire\Deposits;

use App\Models\DepositAccount;
use App\Models\SavingAccount;
use App\Models\Branch;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Livewire\Component;
use Livewire\WithPagination;

class Modification extends Component
{
    use WithPagination, ApprovesActions, LogsActivity, WithLogout;

    public $search = '';
    public $filter_branch = '';
    public $viewMode = 'grid'; // grid | form
    public $selectedAccountId = null;
    public $account = null;
    
    // Modification fields
    public $rollover_type;
    public $saving_account_id;

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
        $this->logActivity('NAVIGATE', 'Perubahan Simpanan Berjangka');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function selectAccount($id)
    {
        $this->account = DepositAccount::with(['cif', 'product'])->find($id);
        if ($this->account) {
            $this->selectedAccountId = $id;
            $this->viewMode = 'form';
            
            // Populate current values
            $this->rollover_type = $this->account->rollover_type;
            $this->saving_account_id = $this->account->saving_account_id;
        }
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedAccountId = null;
        $this->account = null;
    }

    public function submit()
    {
        $this->validate([
            'selectedAccountId' => 'required|exists:deposit_accounts,id',
            'rollover_type' => 'required|in:NONE,PRINCIPAL,PRINCIPAL_INTEREST',
            'saving_account_id' => 'required_if:rollover_type,PRINCIPAL|nullable|exists:saving_accounts,id',
        ]);

        $data = [
            'rollover_type' => $this->rollover_type,
            'saving_account_id' => $this->saving_account_id,
        ];

        // Approval check
        $status = $this->interceptAction('deposits.modification', 'UPDATE', $data, $this->selectedAccountId);

        $this->logActivity('UPDATE_DEPOSIT_INSTRUCTION', "Mengajukan perubahan instruksi rollover untuk simpanan berjangka [{$this->account->account_no}]");

        if ($status === 'PENDING') {
            session()->flash('success', 'Perubahan instruksi rollover telah diajukan ke antrean persetujuan.');
        } else {
            // Auto Approval: Langsung update record
            $this->account->update($data);
            session()->flash('success', 'Instruksi rollover berhasil diperbarui.');
        }

        return redirect()->route('deposits.inquiry');
    }

    public function render()
    {
        $query = DepositAccount::with(['cif', 'product', 'branch', 'bilyet', 'savingAccount'])
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

        $savingAccounts = [];
        if ($this->account && $this->account->cif_id) {
            $savingAccounts = SavingAccount::where('cif_id', $this->account->cif_id)
                ->where('status', 'ACTIVE')
                ->get();
        }

        if ($this->search || $this->filter_branch) {
            $items = $query->orderBy('id', 'desc')->paginate(10);
        } else {
            $items = DepositAccount::whereRaw('1 = 0')->paginate(10);
        }

        return view('livewire.deposits.modification', [
            'items' => $items,
            'savingAccounts' => $savingAccounts,
            'branches' => Branch::where('is_active', true)->get()
        ])->layout('layouts.app');
    }
}
