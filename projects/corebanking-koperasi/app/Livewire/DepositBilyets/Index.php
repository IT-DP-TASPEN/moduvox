<?php

namespace App\Livewire\DepositBilyets;

use App\Models\Branch;
use App\Models\DepositBilyet;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithLogout;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithLogout, ApprovesActions, LogsActivity;

    public $search = '';
    public $statusFilter = '';
    public $branchFilter = '';
    
    public $viewMode = 'list'; // list, register
    
    // Register Form Fields
    public $prefix = 'KSM/SB';
    public $month;
    public $year;
    public $start_sequence;
    public $end_sequence;
    public $branch_id;
    public $padding = 5;

    public $user, $role;

    public function mount()
    {
        $this->user = Auth::user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        
        $this->month = date('m');
        $this->year = date('Y');
        
        // Default branch to user's branch
        $this->branch_id = $this->user->branch_id;
        $this->branchFilter = ($this->role === 'Admin') ? '' : $this->user->branch_id;
        
        $this->logActivity('NAVIGATE', 'Manajemen Bilyet');
    }

    public function setView($mode)
    {
        $this->viewMode = $mode;
        if ($mode === 'register') {
            $this->resetValidation();
        }
    }

    public function registerBilyets()
    {
        $this->validate([
            'prefix' => 'required|string|max:15',
            'month' => 'required|string|size:2',
            'year' => 'required|string|size:4',
            'start_sequence' => 'required|integer|min:1',
            'end_sequence' => 'required|integer|gte:start_sequence',
            'branch_id' => 'required|exists:branches,id',
            'padding' => 'required|integer|min:1|max:10',
        ]);

        $count = ($this->end_sequence - $this->start_sequence) + 1;
        
        if ($count > 500) {
            $this->addError('end_sequence', 'Maksimal 500 bilyet per pendaftaran.');
            return;
        }

        $data = [
            'prefix' => $this->prefix,
            'month' => $this->month,
            'year' => $this->year,
            'start_sequence' => (int)$this->start_sequence,
            'end_sequence' => (int)$this->end_sequence,
            'branch_id' => $this->branch_id,
            'padding' => (int)$this->padding,
            'count' => $count,
        ];

        // Intercept for Approval
        $res = $this->interceptAction('deposit-bilyets', 'CREATE', $data);

        if ($res === 'PENDING') {
            session()->flash('success', "Permintaan pendaftaran {$count} bilyet telah dikirim untuk persetujuan.");
            $this->logActivity('CREATE_REQUEST', "Mengajukan pendaftaran {$count} bilyet simpanan berjangka", null, $data);
        } else {
            (new \App\Services\DepositOperationService())->registerBilyetRange($data);
            session()->flash('success', "Berhasil mendaftarkan {$count} bilyet simpanan berjangka.");
            $this->logActivity('CREATE', "Berhasil mendaftarkan {$count} bilyet simpanan berjangka", null, $data);
        }

        $this->viewMode = 'list';
        $this->reset(['start_sequence', 'end_sequence']);
    }

    public function updateStatus($id, $newStatus)
    {
        $bilyet = DepositBilyet::findOrFail($id);
        $oldStatus = $bilyet->status;
        
        $bilyet->update(['status' => $newStatus]);
        
        $this->logActivity('UPDATE', "Mengubah status bilyet {$bilyet->bilyet_number} dari {$oldStatus} menjadi {$newStatus}");
        session()->flash('success', "Status bilyet {$bilyet->bilyet_number} berhasil diperbarui.");
    }

    public function render()
    {
        $query = DepositBilyet::with(['branch', 'creator']);

        if ($this->search) {
            $query->where('bilyet_number', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->branchFilter) {
            $query->where('branch_id', $this->branchFilter);
        }

        return view('livewire.deposit-bilyets.index', [
            'bilyets' => $query->latest()->paginate(20),
            'branches' => Branch::where('is_active', true)->get(),
        ])->layout('layouts.app');
    }
}
