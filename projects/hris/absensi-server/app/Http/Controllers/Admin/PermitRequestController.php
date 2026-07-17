<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermitRequest;
use Illuminate\Http\Request;

class PermitRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PermitRequest::with('user');
        
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

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Month
        if ($request->filled('month')) {
            $query->whereMonth('requested_at', $request->month);
            if ($request->filled('year')) {
                $query->whereYear('requested_at', $request->year);
            } else {
                $query->whereYear('requested_at', date('Y'));
            }
        }

        // Filter Type
        if ($request->filled('type')) {
            $query->where('permit_type', $request->type);
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        return view('admin.permit_requests.index', compact('items'));
    }

    public function updateStatus(Request $request, PermitRequest $permitRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $permitRequest->status = $validated['status'];
        $permitRequest->admin_note = $validated['admin_note'] ?? null;
        $permitRequest->approved_by = null;
        $permitRequest->approved_at = null;

        if (in_array($validated['status'], ['approved', 'rejected'], true)) {
            $permitRequest->approved_by = optional(auth()->user())->id;
            $permitRequest->approved_at = now();
            
            // Send Notification
            if ($permitRequest->user) {
                $permitRequest->user->notify(new \App\Notifications\RequestStatusUpdated('Izin', $validated['status']));
            }
        }

        $permitRequest->save();

        return redirect()->route('admin.permit_requests.index')->with('success', 'Status izin berhasil diupdate');
    }
}

