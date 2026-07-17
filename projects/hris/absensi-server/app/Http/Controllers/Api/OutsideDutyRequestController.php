<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OutsideDutyRequest;
use Illuminate\Http\Request;
use App\Services\AttendanceSummaryService;

class OutsideDutyRequestController extends Controller
{
    protected $summaryService;

    public function __construct(AttendanceSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    public function index(Request $request)
    {
        $items = OutsideDutyRequest::where('user_id', $request->user()->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return response()->json($items);
    }

    public function pendingApprovals(Request $request)
    {
        try {
            $user = $request->user();
            $approverDivisions = \App\Models\DivisionApprover::where('approver_id', $user->id)->pluck('division_name')->toArray();
            $directorDivisions = \App\Models\DivisionApprover::where('director_id', $user->id)->pluck('division_name')->toArray();

            $items = OutsideDutyRequest::where('status', 'pending')
                ->whereHas('user', function($query) use ($approverDivisions, $directorDivisions, $user) {
                    $query->where(function($q) use ($approverDivisions, $user) {
                        $q->whereIn('division_name', $approverDivisions)->where('id', '!=', $user->id);
                    })->orWhere(function($q) use ($directorDivisions, $user) {
                        $q->whereIn('division_name', $directorDivisions)->whereHas('approverSettings');
                    });
                })
                ->with('user')
                ->latest()
                ->get();

            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function approve(Request $request, $id)
    {
        $item = OutsideDutyRequest::findOrFail($id);
        if ($item->status !== 'pending') return response()->json(['message' => 'Request already processed'], 422);

        $item->status = 'approved';
        $item->approved_at = now();
        $item->approved_by = $request->user()->id;
        $item->save();

        $this->summaryService->updateDailySummary($item->user_id, $item->start_at);

        try {
            $item->user->notify(new \App\Notifications\RequestStatusUpdated('Tugas Luar', 'approved'));
        } catch (\Exception $e) {}

        return response()->json(['message' => 'Pengajuan berhasil disetujui', 'data' => $item]);
    }

    public function reject(Request $request, $id)
    {
        $item = OutsideDutyRequest::findOrFail($id);
        if ($item->status !== 'pending') return response()->json(['message' => 'Request already processed'], 422);

        $item->status = 'rejected';
        $item->approved_at = now();
        $item->approved_by = $request->user()->id;
        $item->save();

        try {
            $item->user->notify(new \App\Notifications\RequestStatusUpdated('Tugas Luar', 'rejected'));
        } catch (\Exception $e) {}

        return response()->json(['message' => 'Pengajuan berhasil ditolak', 'data' => $item]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'overtime_minutes' => 'nullable|integer|min:0|max:1440',
            'break_minutes' => 'nullable|integer|min:0|max:1440',
            'notes' => 'nullable|string|max:2000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photo' => 'nullable|file|mimes:jpeg,jpg,png,webp,heic,heif|max:20480',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('outside_duty_requests', 'public');
        }

        $item = OutsideDutyRequest::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'],
            'overtime_minutes' => (int) ($validated['overtime_minutes'] ?? 0),
            'break_minutes' => (int) ($validated['break_minutes'] ?? 0),
            'notes' => $validated['notes'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'photo_path' => $photoPath,
            'status' => 'pending',
        ]);

        $approver = \App\Models\DivisionApprover::where('division_name', $request->user()->division_name)->first();
        if ($approver) {
            $approverUser = $approver->approver_id == $request->user()->id ? $approver->director : $approver->approver;
            if ($approverUser) {
                $approverUser->notify(new \App\Notifications\RequestSubmitted('Tugas Luar', $request->user()->name));
            }
        }

        return response()->json([
            'message' => 'Pengajuan tugas luar berhasil dikirim',
            'data' => $item,
        ]);
    }
}

