<?php

namespace App\Livewire\Savings;

use App\Models\SavingAccount;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Transfer extends Component
{
    use ApprovesActions, LogsActivity, WithLogout, WithPagination;

    public $search = '';
    public $viewMode = 'list'; // list, form
    
    public $fromAccountId = null;
    public $totalResults = 0;
    public $toAccountId = null;
    public $to_account_no = '';
    public $amount = 0;
    public $reference_no = '';
    public $description = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'viewMode' => ['except' => 'list']
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function selectFromAccount($id)
    {
        $this->fromAccountId = $id;
        $this->viewMode = 'form';
        
        // Auto-generate unique reference: purely random alphanumeric
        $this->reference_no = strtoupper(bin2hex(random_bytes(6)));
        $this->description = 'Transfer Antar Rekening';
        
        $this->logActivity('TRANSFER_SELECT_FROM', "Memilih rekening pengirim ID [{$id}]");
    }

    public function updatedToAccountNo()
    {
        $this->toAccountId = null;
    }

    public function selectToAccount($id)
    {
        $account = SavingAccount::findOrFail($id);
        $this->toAccountId = $id;
        $this->to_account_no = $account->account_no;
        $this->logActivity('TRANSFER_SELECT_TO', "Memilih rekening penerima [{$this->to_account_no}]");
    }

    public function closeView()
    {
        $this->viewMode = 'list';
        $this->reset(['fromAccountId', 'toAccountId', 'to_account_no', 'amount', 'reference_no', 'description']);
    }

    public function submit()
    {
        $fromAccount = SavingAccount::findOrFail($this->fromAccountId);
        $toAccount = SavingAccount::findOrFail($this->toAccountId);

        $this->validate([
            'to_account_no' => 'required|exists:saving_accounts,account_no|different:fromAccountId',
            'amount' => 'required|numeric|min:100',
            'reference_no' => 'required|min:3',
            'description' => 'required|min:5'
        ]);

        // Security Checks
        if ($fromAccount->status !== 'ACTIVE') {
            $this->addError('amount', "Rekening pengirim sedang dalam status {$fromAccount->status}.");
            return;
        }

        if ($toAccount->status !== 'ACTIVE') {
            $this->addError('to_account_no', "Rekening penerima sedang dalam status {$toAccount->status}.");
            return;
        }

        if ($this->amount > $fromAccount->effective_balance) {
            $this->addError('amount', "Saldo pengirim tidak mencukupi. Saldo tersedia: Rp " . number_format($fromAccount->effective_balance, 2, ',', '.'));
            return;
        }

        $data = [
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => (float)$this->amount,
            'reference_no' => $this->reference_no,
            'description' => $this->description,
            'action' => 'TRANSFER',
            'requested_by' => Auth::id(),
            'branch_id' => Auth::user()->branch_id,
        ];

        $status = $this->interceptAction('savings.transfer', 'TRANSFER', $data);

        $this->logActivity('TRANSFER_REQUEST', "Mengajukan transfer Rp " . number_format($this->amount, 2, ',', '.') . " dari [{$fromAccount->account_no}] ke [{$this->to_account_no}]");

        if ($status === 'PENDING') {
            session()->flash('success', 'Permohonan transfer telah diajukan ke antrean persetujuan.');
        } else {
            $service = new \App\Services\SavingOperationService();
            $service->postTransfer($fromAccount, $toAccount, $this->amount, $this->description, $this->reference_no);
            session()->flash('success', 'Transfer berhasil diposting.');
        }

        return redirect()->route('savings.inquiry');
    }

    public function mount()
    {
        $accountNo = request()->query('account');
        if ($accountNo) {
            $account = SavingAccount::where('account_no', $accountNo)->first();
            if ($account) {
                $this->fromAccountId = $account->id;
                $this->viewMode = 'form';
                // Auto-generate unique reference: purely random alphanumeric
                $this->reference_no = strtoupper(bin2hex(random_bytes(6)));
                $this->description = 'Transfer Antar Rekening';
            }
        }
        $this->logActivity('NAVIGATE', 'Transfer Antar Rekening');
    }

    public function render()
    {
        $query = SavingAccount::with(['cif', 'product'])->where('status', 'ACTIVE');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('account_no', 'like', '%' . $this->search . '%')
                  ->orWhereHas('cif', function($sq) {
                      $sq->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('nik', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if (!empty(trim($this->search))) {
            $items = $query->orderBy('id', 'desc')->distinct()->paginate(10);
            $this->totalResults = $items->total();
        } else {
            $items = $query->whereRaw('1 = 0')->paginate(1);
            $this->totalResults = 0;
        }

        $toAccountResults = [];
        if (strlen($this->to_account_no) >= 3 && !$this->toAccountId) {
            $toAccountResults = SavingAccount::with(['cif', 'product'])
                ->where('status', 'ACTIVE')
                ->where('id', '!=', $this->fromAccountId)
                ->where(function($q) {
                    $q->where('account_no', 'like', '%' . $this->to_account_no . '%')
                      ->orWhereHas('cif', function($sq) {
                          $sq->where('name', 'like', '%' . $this->to_account_no . '%');
                      });
                })
                ->limit(5)
                ->get();
        }

        $fromAccount = null;
        if ($this->fromAccountId) {
            $fromAccount = SavingAccount::with(['cif', 'product'])->find($this->fromAccountId);
        }

        $toAccount = null;
        if ($this->toAccountId) {
            $toAccount = SavingAccount::with(['cif', 'product'])->find($this->toAccountId);
        }

        return view('livewire.savings.transfer', [
            'items' => $items,
            'toAccountResults' => $toAccountResults,
            'fromAccount' => $fromAccount,
            'toAccount' => $toAccount,
            'user' => Auth::user(),
            'role' => Auth::user()->getRoleNames()->first()
        ])->layout('layouts.app');
    }
}
