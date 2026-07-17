<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['user', 'approver']);
        
        $user = auth()->user();
        if ($user && !$user->is_admin) {
            $asApprover = \App\Models\OfficeApprover::where('approver_id', $user->id)->pluck('office_id');
            $asDirector = \App\Models\OfficeApprover::where('director_id', $user->id)->pluck('office_id');

            $query->whereHas('user', function($q) use ($asApprover, $asDirector) {
                $q->whereIn('office_id', $asApprover)
                  ->orWhereIn('office_id', $asDirector);
            });
        }

        // Filter Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Month
        if ($request->filled('month')) {
            $query->whereMonth('start_date', $request->month);
            if ($request->filled('year')) {
                $query->whereYear('start_date', $request->year);
            } else {
                $query->whereYear('start_date', date('Y'));
            }
        }

        // Filter Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        return view('admin.leave_requests.index', compact('items'));
    }

    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan sudah pernah diproses.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $leaveRequest->status = $validated['status'];
        $leaveRequest->admin_note = $validated['admin_note'] ?? null;
        $leaveRequest->approved_by = optional(auth()->user())->id;
        $leaveRequest->approved_at = now();

        // Potong sisa cuti jika jenisnya Cuti Tahunan dan status disetujui
        if ($validated['status'] === 'approved' && $leaveRequest->type === 'Cuti Tahunan') {
            $user = $leaveRequest->user;
            $startDate = \Carbon\Carbon::parse($leaveRequest->start_date);
            $endDate = \Carbon\Carbon::parse($leaveRequest->end_date);
            $days = $startDate->diffInDays($endDate) + 1;

            $employment = $user->employment;
            if ($employment) {
                $employment->remaining_leave -= $days;
                $employment->save();
            }
        }

        // Send Notification
        if ($leaveRequest->user) {
            $leaveRequest->user->notify(new \App\Notifications\RequestStatusUpdated('Cuti', $validated['status']));
        }

        $leaveRequest->save();

        return redirect()->route('admin.leave_requests.index')->with('success', 'Status cuti berhasil diupdate');
    }
}

