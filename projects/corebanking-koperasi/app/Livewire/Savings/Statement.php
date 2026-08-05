<?php

namespace App\Livewire\Savings;

use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;

class Statement extends Component
{
    use WithPagination, LogsActivity;

    public $search = '';
    public $startDate = '';
    public $endDate = '';
    public $selectedAccountId = null;
    public $viewMode = 'list';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->logActivity('NAVIGATE', 'Cetak Rekening Koran');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectAccount($id)
    {
        $this->selectedAccountId = $id;
        $this->viewMode = 'statement';
    }

    public function closeView()
    {
        $this->viewMode = 'list';
        $this->selectedAccountId = null;
    }

    public function render()
    {
        $query = SavingAccount::with(['cif', 'product']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('account_no', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cif', function ($sq) {
                        $sq->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('nik', 'like', '%' . $this->search . '%')
                            ->orWhere('cif_no', 'like', '%' . $this->search . '%');
                    });
            });
            $accounts = $query->orderBy('id', 'desc')->paginate(10);
        } else {
            $accounts = $query->whereRaw('1 = 0')->paginate(10);
        }

        $selectedAccount = null;
        $transactions = collect();
        if ($this->selectedAccountId) {
            $selectedAccount = SavingAccount::with(['cif', 'product', 'branch'])->find($this->selectedAccountId);
            
            $txQuery = SavingTransaction::with('originalTransaction')
                ->where('saving_account_id', $this->selectedAccountId)
                ->orderBy('created_at', 'asc');
                
            if ($this->startDate) {
                $txQuery->whereDate('created_at', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $txQuery->whereDate('created_at', '<=', $this->endDate);
            }
            
            $transactions = $txQuery->get();
        }

        return view('livewire.savings.statement', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'transactions' => $transactions,
            'user' => Auth::user(),
            'role' => Auth::user()->getRoleNames()->first() ?? 'No Role'
        ])->layout('layouts.app');
    }
}
