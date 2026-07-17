<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutsideDutyRequest;
use Illuminate\Http\Request;

class OutsideDutyRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = OutsideDutyRequest::with('user');
        
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
        return view('admin.outside_duty_requests.index', compact('items'));
    }

    public function updateStatus(Request $request, OutsideDutyRequest $outsideDutyRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $outsideDutyRequest->status = $validated['status'];
        $outsideDutyRequest->admin_note = $validated['admin_note'] ?? null;
        $outsideDutyRequest->approved_by = null;
        $outsideDutyRequest->approved_at = null;

        if (in_array($validated['status'], ['approved', 'rejected'], true)) {
            $outsideDutyRequest->approved_by = optional(auth()->user())->id;
            $outsideDutyRequest->approved_at = now();
            
            // Send Notification
            if ($outsideDutyRequest->user) {
                $outsideDutyRequest->user->notify(new \App\Notifications\RequestStatusUpdated('Tugas Luar', $validated['status']));
            }
        }

        $outsideDutyRequest->save();

        return redirect()->route('admin.outside_duty_requests.index')->with('success', 'Status tugas luar berhasil diupdate');
    }
}

