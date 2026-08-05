<?php

namespace App\Livewire\AuditLogs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithLogout, LogsActivity;

    public $search = '';
    public $filter_user = '';
    public $filter_action = '';
    
    // Sidebar info
    public $user;
    public $role;
    public $company;
    public $branch;

    public function mount()
    {
        $this->user = Auth::user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->company = Company::find($this->user->company_id);
        $this->branch = Branch::find($this->user->branch_id);

        $this->logActivity('NAVIGATE', 'Audit Log');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterUser()
    {
        $this->resetPage();
    }

    public function updatedFilterAction()
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::with('user')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                      ->orWhere('action', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filter_user, function($query) {
                $query->where('user_id', $this->filter_user);
            })
            ->when($this->filter_action, function($query) {
                $query->where('action', $this->filter_action);
            })
            ->latest()
            ->paginate(15);

        return view('livewire.audit-logs.index', [
            'logsList' => $logs,
            'allUsers' => User::all(),
            'actions' => ActivityLog::select('action')->distinct()->get()
        ])->layout('layouts.app');
    }
}
