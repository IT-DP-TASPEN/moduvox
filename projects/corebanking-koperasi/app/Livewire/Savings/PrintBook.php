<?php

namespace App\Livewire\Savings;

use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;
use Livewire\Component;
use Livewire\WithPagination;

class PrintBook extends Component
{
    use WithLogout, WithPagination, LogsActivity;

    public $search = '';
    public $viewMode = 'list'; // list, form
    
    public $selectedAccount = null;
    public $dateFrom;
    public $dateTo;
    public $totalResults = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'viewMode' => ['except' => 'list']
    ];

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Cetak Buku Tabungan');

        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function selectAccount($id)
    {
        $this->selectedAccount = SavingAccount::with(['cif', 'product'])->find($id);
        if ($this->selectedAccount) {
            $this->viewMode = 'form';
        }
    }

    public function closeView()
    {
        $this->viewMode = 'list';
        $this->reset(['selectedAccount']);
    }

    public function getHistoryProperty()
    {
        if (!$this->selectedAccount) return collect();

        return SavingTransaction::where('saving_account_id', $this->selectedAccount->id)
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function render()
    {
        $items = collect();
        if (!empty(trim($this->search))) {
            $items = SavingAccount::with(['cif', 'product'])
                ->where(function($q) {
                    $q->where('account_no', 'like', '%' . $this->search . '%')
                      ->orWhereHas('cif', function($sq) {
                          $sq->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('nik', 'like', '%' . $this->search . '%');
                      });
                })
                ->orderBy('id', 'desc')
                ->distinct()
                ->paginate(10);
            
            $this->totalResults = $items->total();
        } else {
            $this->totalResults = 0;
            $items = SavingAccount::whereRaw('1 = 0')->paginate(1);
        }

        return view('livewire.savings.print-book', [
            'items' => $items,
            'history' => $this->history,
            'user' => auth()->user(),
            'role' => auth()->user()->getRoleNames()->first()
        ])->layout('layouts.app');
    }
}
