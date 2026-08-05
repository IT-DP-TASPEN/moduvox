<?php

namespace App\Livewire\Approvals;

use App\Models\ApprovalRequest;
use Livewire\Component;

class Badge extends Component
{
    protected $listeners = [
        'approval-created' => '$refresh',
        'approval-processed' => '$refresh'
    ];

    public function render()
    {
        $query = ApprovalRequest::where('status', 'PENDING');
        
        $user = auth()->user();
        
        // Cek jika ada user login dan role BUKAN Admin
        if ($user && $user->getRoleNames()->first() !== 'Admin') {
            $query->whereHas('requester', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->where('company_id', $user->company_id);
            });
        }
        
        $count = $query->count();
        
        return view('livewire.approvals.badge', [
            'count' => $count
        ]);
    }
}
