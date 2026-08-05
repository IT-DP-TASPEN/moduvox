<?php

namespace App\Livewire\Savings\Status;

use App\Models\SavingAccount;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Reactivate extends Component
{
    use ApprovesActions, LogsActivity, WithLogout, WithPagination;

    public $search = '';
    public $viewMode = 'list'; // list, form
    
    public $selectedAccount = null;
    public $reason = '';
    public $totalResults = 0;

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
        $this->selectedAccount = SavingAccount::with(['cif', 'product'])
            ->where('id', $id)
            ->first();

        if ($this->selectedAccount) {
            $this->viewMode = 'form';
            $this->logActivity('REACTIVATE_SELECT_ACCOUNT', "Memilih rekening [{$this->selectedAccount->account_no}] untuk reaktivasi");
        }
    }

    public function closeView()
    {
        $this->viewMode = 'list';
        $this->reset(['selectedAccount', 'reason']);
    }

    public function submit()
    {
        $this->validate([
            'reason' => 'required|min:10'
        ]);

        $data = [
            'account_no' => $this->selectedAccount->account_no,
            'description' => $this->reason,
            'branch_id' => Auth::user()->branch_id,
        ];

        $status = $this->interceptAction('savings.reactivate', 'REACTIVATE', $data, $this->selectedAccount->id);

        $this->logActivity('REACTIVATE_SAVING_REQUEST', "Mengajukan reaktivasi status untuk rekening [{$this->selectedAccount->account_no}]");

        if ($status === 'PENDING') {
            session()->flash('success', 'Permohonan reaktivasi rekening telah diajukan ke antrean persetujuan.');
        } else {
            session()->flash('success', 'Rekening berhasil diaktifkan kembali.');
        }

        return redirect()->route('savings.inquiry');
    }

    public function mount()
    {
        $accountNo = request()->query('account');
        if ($accountNo) {
            $account = SavingAccount::where('account_no', $accountNo)->first();
            if ($account) {
                $this->selectAccount($account->id);
            }
        }
        $this->logActivity('NAVIGATE', 'Aktivasi Rekening');
    }

    public function render()
    {
        $query = SavingAccount::with(['cif', 'product'])
            ->where('status', '!=', 'ACTIVE');

        $items = collect();
        if (!empty(trim($this->search))) {
            $query->where(function($q) {
                $q->where('account_no', 'like', '%' . $this->search . '%')
                  ->orWhereHas('cif', function($sq) {
                      $sq->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('nik', 'like', '%' . $this->search . '%');
                  });
            });

            $items = $query->orderBy('id', 'desc')->distinct()->paginate(10);
            $this->totalResults = $items->total();
        } else {
            $this->totalResults = 0;
            $items = SavingAccount::whereRaw('1 = 0')->paginate(1);
        }

        return view('livewire.savings.status.reactivate', [
            'items' => $items,
            'user' => Auth::user(),
            'role' => Auth::user()->getRoleNames()->first()
        ])->layout('layouts.app');
    }
}
