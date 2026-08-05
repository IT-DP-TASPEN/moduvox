<?php

namespace App\Livewire\Savings;

use App\Models\SavingTransaction;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Reversal extends Component
{
    use WithPagination, ApprovesActions, LogsActivity, WithLogout;

    public $search = '';
    public $viewMode = 'list'; // list, form
    public $reason = '';
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
        $this->selectedTrx = SavingTransaction::with(['account.cif'])->findOrFail($id);
        if ($this->selectedTrx) {
            $this->viewMode = 'form';
            $this->logActivity('REVERSAL_SELECT_TRX', "Memilih transaksi [{$this->selectedTrx->transaction_no}] untuk koreksi");
        }
    }

    public function closeView()
    {
        $this->viewMode = 'list';
        $this->reset(['selectedTrx', 'reason']);
    }

    public function submitReversal()
    {
        $this->validate([
            'reason' => 'required|min:5'
        ]);

        if ($this->selectedTrx->type === 'REVERSAL') {
            $this->addError('reason', 'Transaksi Reversal tidak dapat di-reversal kembali.');
            return;
        }

        if (SavingTransaction::where('type', 'REVERSAL')->where('reference_no', $this->selectedTrx->transaction_no)->exists()) {
            $this->addError('reason', 'Transaksi ini sudah pernah di-reversal.');
            return;
        }

        $data = [
            'original_transaction_no' => $this->selectedTrx->transaction_no,
            'description' => $this->reason,
            'amount' => $this->selectedTrx->amount,
            'account_no' => $this->selectedTrx->account->account_no,
            'action' => 'REVERSAL',
            'requested_by' => Auth::id(),
            'branch_id' => Auth::user()->branch_id,
        ];

        // Intercept for approval
        $status = $this->interceptAction('savings.reversal', 'REVERSAL', $data, $this->selectedTrx->id);

        $this->logActivity('REVERSAL_REQUEST', "Mengajukan koreksi/reversal transaksi [{$this->selectedTrx->transaction_no}]");

        if ($status === 'PENDING') {
            session()->flash('success', 'Permohonan koreksi (reversal) telah diajukan ke antrean persetujuan.');
        } else {
            session()->flash('success', 'Koreksi berhasil diposting.');
        }

        return redirect()->route('savings.reversal');
    }

    public function mount()
    {
        $accountNo = request()->query('account');
        if ($accountNo) {
            $trx = SavingTransaction::with(['account.cif'])
                ->whereHas('account', function($q) use ($accountNo) {
                    $q->where('account_no', $accountNo);
                })
                ->where('type', '!=', 'REVERSAL')
                ->latest()
                ->first();
            
            if ($trx) {
                $this->selectTrx($trx->id);
            }
        }
        $this->logActivity('NAVIGATE', 'Reversal Transaksi');
    }

    public function render()
    {
        $query = SavingTransaction::with(['account.cif'])
            ->where('type', '!=', 'REVERSAL')
            ->whereDoesntHave('reversalTransaction');

        $transactions = collect();
        if (!empty(trim($this->search))) {
            $query->where(function($q) {
                $q->where('transaction_no', 'like', '%' . $this->search . '%')
                  ->orWhereHas('account', function($sq) {
                      $sq->where('account_no', 'like', '%' . $this->search . '%')
                        ->orWhereHas('cif', function($ssq) {
                            $ssq->where('name', 'like', '%' . $this->search . '%');
                        });
                  });
            });

            $transactions = $query->orderBy('id', 'desc')->distinct()->paginate(10);
            $this->totalResults = $transactions->total();
        } else {
            $this->totalResults = 0;
            $transactions = SavingTransaction::whereRaw('1 = 0')->paginate(1);
        }

        return view('livewire.savings.reversal', [
            'transactions' => $transactions,
            'user' => Auth::user(),
            'role' => Auth::user()->getRoleNames()->first()
        ])->layout('layouts.app');
    }
}
