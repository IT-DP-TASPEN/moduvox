<?php

namespace App\Livewire\Savings;

use App\Models\SavingTransaction;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;
use Livewire\Component;
use Livewire\WithPagination;

class PrintSlip extends Component
{
    use WithLogout, WithPagination, LogsActivity;

    public $search = '';
    public $viewMode = 'list'; // list, form
    public $selectedTrx = null;
    public $totalResults = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'viewMode' => ['except' => 'list']
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function selectTrx($id)
    {
        $this->selectedTrx = SavingTransaction::with(['account.cif', 'account.product', 'account.branch'])->find($id);
        if ($this->selectedTrx) {
            $this->viewMode = 'form';
        }
    }

    public function closeView()
    {
        $this->viewMode = 'list';
        $this->reset(['selectedTrx']);
    }

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Cetak Slip Transaksi');
    }

    public function render()
    {
        $items = collect();
        if (!empty(trim($this->search))) {
            $items = SavingTransaction::with(['account.cif'])
                ->where(function($q) {
                    $q->where('transaction_no', 'like', '%' . $this->search . '%')
                      ->orWhereHas('account', function($sq) {
                          $sq->where('account_no', 'like', '%' . $this->search . '%')
                            ->orWhereHas('cif', function($ssq) {
                                $ssq->where('name', 'like', '%' . $this->search . '%');
                            });
                      });
                })
                ->orderBy('id', 'desc')
                ->distinct()
                ->paginate(10);
            
            $this->totalResults = $items->total();
        } else {
            $this->totalResults = 0;
            $items = SavingTransaction::whereRaw('1 = 0')->paginate(1);
        }

        return view('livewire.savings.print-slip', [
            'items' => $items,
            'user' => auth()->user(),
            'role' => auth()->user()->getRoleNames()->first()
        ])->layout('layouts.app');
    }
}
