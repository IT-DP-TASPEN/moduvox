<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;

class OvertimeRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = OvertimeRequest::with('user');
        
        $user = auth()->user();
        if ($user && !$user->is_admin) {
            $asApprover = \App\Models\DivisionApprover::where('approver_id', $user->id)->pluck('division_name');
            $asDirector = \App\Models\DivisionApprover::where('director_id', $user->id)->pluck('approver_id');

            $query->whereHas('user', function($q) use ($asApprover, $asDirector) {
                $q->whereIn('division_name', $asApprover)
                  ->orWhereIn('id', $asDirector);
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

        // Filter Month
        if ($request->filled('month')) {
            $query->whereMonth('start_at', $request->month);
            if ($request->filled('year')) {
                $query->whereYear('start_at', $request->year);
            } else {
                $query->whereYear('start_at', date('Y'));
            }
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        return view('admin.overtime_requests.index', compact('items'));
    }

    public function updateStatus(Request $request, OvertimeRequest $overtimeRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $overtimeRequest->status = $validated['status'];
        $overtimeRequest->admin_note = $validated['admin_note'] ?? null;
        $overtimeRequest->approved_by = null;
        $overtimeRequest->approved_at = null;

        if (in_array($validated['status'], ['approved', 'rejected'], true)) {
            $overtimeRequest->approved_by = optional(auth()->user())->id;
            $overtimeRequest->approved_at = now();
            
            // Send Notification
            if ($overtimeRequest->user) {
                $overtimeRequest->user->notify(new \App\Notifications\RequestStatusUpdated('Lembur', $validated['status']));
            }
        }

        $overtimeRequest->save();

        return redirect()->route('admin.overtime_requests.index')->with('success', 'Status lembur berhasil diupdate');
    }
}

