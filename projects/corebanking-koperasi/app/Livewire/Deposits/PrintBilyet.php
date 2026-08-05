<?php

namespace App\Livewire\Deposits;

use App\Models\DepositAccount;
use App\Models\Branch;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;
use Livewire\Component;
use Livewire\WithPagination;

class PrintBilyet extends Component
{
    use WithPagination, WithLogout, LogsActivity;

    public $search = '';
    public $filter_branch = '';
    public $viewMode = 'list'; // list, preview
    public $selectedAccountId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter_branch' => ['except' => ''],
        'viewMode' => ['except' => 'list'],
        'selectedAccountId' => ['except' => null]
    ];

    public function mount()
    {
        if ($this->selectedAccountId) {
            $this->selectAccount($this->selectedAccountId);
        }
        $this->logActivity('NAVIGATE', 'Cetak Bilyet Simpanan Berjangka');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function selectAccount($id)
    {
        $this->selectedAccountId = $id;
        $this->viewMode = 'preview';
        $this->logActivity('PRINT_BILYET', "Mencetak bilyet untuk rekening ID [{$id}]");
    }

    public function closeView()
    {
        $this->viewMode = 'list';
        $this->selectedAccountId = null;
    }

    public function render()
    {
        $query = DepositAccount::with(['cif', 'product', 'branch', 'bilyet'])
            ->where('status', 'ACTIVE');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('account_no', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cif', function ($qc) {
                        $qc->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('bilyet', function ($bq) {
                        $bq->where('kode_bilyet', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filter_branch) {
            $query->where('branch_id', $this->filter_branch);
        }

        $selectedAccount = null;
        if ($this->selectedAccountId) {
            $selectedAccount = DepositAccount::with(['cif', 'product', 'branch', 'bilyet'])->find($this->selectedAccountId);
        }

        if ($this->search || $this->filter_branch) {
            $items = $query->orderBy('id', 'desc')->paginate(10);
        } else {
            $items = DepositAccount::whereRaw('1 = 0')->paginate(10);
        }

        return view('livewire.deposits.print-bilyet', [
            'items' => $items,
            'selectedAccount' => $selectedAccount,
            'branches' => Branch::where('is_active', true)->get(),
            'user' => auth()->user(),
            'role' => auth()->user()->getRoleNames()->first()
        ])->layout('layouts.app');
    }
}
