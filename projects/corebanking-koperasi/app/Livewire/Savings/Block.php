<?php

namespace App\Livewire\Savings;

use App\Models\SavingAccount;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Block extends Component
{
    use ApprovesActions, LogsActivity, WithLogout, WithPagination;

    public $search = '';
    public $viewMode = 'list'; // list, form
    public $totalResults = 0;
    
    public $selectedAccountId = null;
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

    public function selectAccount($id)
    {
        $this->selectedAccountId = $id;
        $this->viewMode = 'form';
        
        // Auto-generate unique reference: purely random alphanumeric
        $this->reference_no = strtoupper(bin2hex(random_bytes(6)));
        $this->description = 'Pemblokiran Saldo Simpanan';
        
        $this->logActivity('BLOCK_SELECT_ACCOUNT', "Memilih rekening ID [{$id}] untuk blokir saldo");
    }

    public function closeView()
    {
        $this->viewMode = 'list';
        $this->reset(['selectedAccountId', 'amount', 'reference_no', 'description']);
    }

    public function submit()
    {
        $account = SavingAccount::findOrFail($this->selectedAccountId);

        $this->validate([
            'amount' => 'required|numeric|min:100',
            'reference_no' => 'required|min:3',
            'description' => 'required|min:5'
        ]);

        if ($this->amount > $account->effective_balance) {
            $this->addError('amount', "Nominal blokir melebihi saldo efektif yang tersedia. Saldo tersedia: Rp " . number_format($account->effective_balance, 2, ',', '.'));
            return;
        }

        $data = [
            'saving_account_id' => $account->id,
            'account_no' => $account->account_no,
            'amount' => (float)$this->amount,
            'reference_no' => $this->reference_no,
            'description' => $this->description,
            'type' => 'BLOCK',
            'branch_id' => Auth::user()->branch_id,
        ];

        $status = $this->interceptAction('savings.block', 'BLOCK', $data);

        $this->logActivity('BLOCK_REQUEST', "Mengajukan blokir saldo Rp " . number_format($this->amount, 2, ',', '.') . " pada rekening [{$account->account_no}]");

        if ($status === 'PENDING') {
            session()->flash('success', 'Permohonan blokir saldo telah diajukan ke antrean persetujuan.');
        } else {
            $service = new \App\Services\SavingOperationService();
            $service->blockBalance($account, $this->amount, $this->description, $this->reference_no);
            session()->flash('success', 'Saldo berhasil diblokir.');
        }

        return redirect()->route('savings.inquiry');
    }

    public function mount()
    {
        $accountNo = request()->query('account');
        if ($accountNo) {
            $account = SavingAccount::where('account_no', $accountNo)->first();
            if ($account) {
                $this->selectedAccountId = $account->id;
                $this->viewMode = 'form';
                // Auto-generate unique reference: purely random alphanumeric
                $this->reference_no = strtoupper(bin2hex(random_bytes(6)));
                $this->description = 'Pemblokiran Saldo Simpanan';
            }
        }
        $this->logActivity('NAVIGATE', 'Blokir Saldo');
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

        $selectedAccount = null;
        if ($this->selectedAccountId) {
            $selectedAccount = SavingAccount::with(['cif', 'product'])->find($this->selectedAccountId);
        }

        return view('livewire.savings.block', [
            'items' => $items,
            'selectedAccount' => $selectedAccount,
            'user' => Auth::user(),
            'role' => Auth::user()->getRoleNames()->first()
        ])->layout('layouts.app');
    }
}
