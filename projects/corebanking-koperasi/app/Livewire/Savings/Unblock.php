<?php

namespace App\Livewire\Savings;

use App\Models\SavingAccount;
use App\Models\ApprovalRequest;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Livewire\WithPagination;

use App\Traits\ApprovesActions;

class Unblock extends Component
{
    use ApprovesActions, LogsActivity, WithLogout, WithPagination;

    public $search = '';
    public $viewMode = 'list'; // list, form
    public $totalResults = 0;
    
    public $selectedAccountId = null;
    public $selectedBlockId = null;
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
        $account = SavingAccount::find($id);
        if ($account) {
            if ($account->blocked_balance <= 0) {
                session()->flash('warning', 'Rekening ini tidak memiliki saldo yang terblokir.');
                return;
            }
            $this->selectedAccountId = $id;
            $this->viewMode = 'form';
            
            // Auto-generate unique reference: purely random alphanumeric
            $this->reference_no = strtoupper(bin2hex(random_bytes(6)));
            $this->description = 'Pembukaan Blokir Saldo';
            
            $this->logActivity('UNBLOCK_SELECT_ACCOUNT', "Memilih rekening ID [{$id}] untuk buka blokir saldo");
        }
    }

    public function closeView()
    {
        $this->viewMode = 'list';
        $this->reset(['selectedAccountId', 'amount', 'description']);
    }

    public function submit()
    {
        $account = SavingAccount::findOrFail($this->selectedAccountId);

        $this->validate([
            'selectedBlockId' => 'required|exists:saving_blocks,id',
            'description' => 'required|min:5',
        ]);

        $block = \App\Models\SavingBlock::find($this->selectedBlockId);

        $data = [
            'saving_account_id' => $account->id,
            'block_id' => $this->selectedBlockId,
            'amount' => (float)$block->amount,
            'description' => $this->description,
            'reference_no' => $this->reference_no, // Use the unique generated reference
            'branch_id' => Auth::user()->branch_id,
        ];

        $status = $this->interceptAction('savings.unblock', 'UNBLOCK', $data, $account->id);

        $this->logActivity('UNBLOCK_REQUEST', "Mengajukan buka blokir saldo [REF: {$block->reference_no}] untuk rekening [{$account->account_no}]");
        
        if ($status === 'PENDING') {
            session()->flash('success', 'Permintaan buka blokir saldo telah diajukan kepada Supervisor.');
        } else {
            $service = new \App\Services\SavingOperationService();
            $service->unblockBalance($account, $data['amount'], $this->description, $this->selectedBlockId);
            session()->flash('success', 'Blokir saldo berhasil dibuka.');
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
                $this->description = 'Pembukaan Blokir Saldo';
            }
        }
        $this->logActivity('NAVIGATE', 'Buka Blokir Saldo');
    }

    public function render()
    {
        $query = SavingAccount::with(['cif', 'product'])
            ->where('blocked_balance', '>', 0);

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
        $activeBlocks = [];
        if ($this->selectedAccountId) {
            $selectedAccount = SavingAccount::with(['cif', 'product', 'activeBlocks'])->find($this->selectedAccountId);
            if ($selectedAccount) {
                $activeBlocks = $selectedAccount->activeBlocks;
            }
        }

        return view('livewire.savings.unblock', [
            'items' => $items,
            'selectedAccount' => $selectedAccount,
            'activeBlocks' => $activeBlocks,
            'user' => Auth::user(),
            'role' => Auth::user()->getRoleNames()->first()
        ])->layout('layouts.app');
    }
}
