<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Services\AttendanceSummaryService;

class LeaveRequestController extends Controller
{
    protected $summaryService;

    public function __construct(AttendanceSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    public function index(Request $request)
    {
        $items = LeaveRequest::where('user_id', $request->user()->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return response()->json($items);
    }

    public function pendingApprovals(Request $request)
    {
        try {
            $user = $request->user();
            
            // Find divisions where this user is the approver or director
            $approverDivisions = \App\Models\DivisionApprover::where('approver_id', $user->id)
                ->pluck('division_name')
                ->toArray();
                
            $directorDivisions = \App\Models\DivisionApprover::where('director_id', $user->id)
                ->pluck('division_name')
                ->toArray();

            $items = LeaveRequest::where('status', 'pending')
                ->whereHas('user', function($query) use ($approverDivisions, $directorDivisions, $user) {
                    $query->where(function($q) use ($approverDivisions, $user) {
                        // Requests from users in divisions where I am the approver
                        $q->whereIn('division_name', $approverDivisions)
                          ->where('id', '!=', $user->id); // Can't approve own request
                    })->orWhere(function($q) use ($directorDivisions, $user) {
                        // Requests from users in divisions where I am the director
                        // but only if the requester IS the approver of that division
                        $q->whereIn('division_name', $directorDivisions)
                          ->whereHas('approverSettings'); // Requester is an approver somewhere
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
        $item = LeaveRequest::findOrFail($id);
        
        if ($item->status !== 'pending') {
            return response()->json(['message' => 'Request already processed'], 422);
        }

        $item->status = 'approved';
        $item->approved_at = now();
        $item->approved_by = $request->user()->id;
        $item->save();

        // Potong sisa cuti jika jenisnya Cuti Tahunan
        if ($item->type === 'Cuti Tahunan') {
            $user = $item->user;
            $startDate = \Carbon\Carbon::parse($item->start_date);
            $endDate = \Carbon\Carbon::parse($item->end_date);
            $days = $startDate->diffInDays($endDate) + 1;

            $employment = $user->employment;
            if ($employment) {
                $employment->remaining_leave -= $days;
                $employment->save();
            }
        }

        // Update attendance summary for each day of leave
        $start = \Carbon\Carbon::parse($item->start_date);
        $end = \Carbon\Carbon::parse($item->end_date);
        while ($start <= $end) {
            $this->summaryService->updateDailySummary($item->user_id, $start);
            $start->addDay();
        }

        try {
            $item->user->notify(new \App\Notifications\RequestStatusUpdated('Cuti', 'approved'));
        } catch (\Exception $e) {}

        return response()->json(['message' => 'Pengajuan berhasil disetujui', 'data' => $item]);
    }

    public function reject(Request $request, $id)
    {
        $item = LeaveRequest::findOrFail($id);
        
        if ($item->status !== 'pending') {
            return response()->json(['message' => 'Request already processed'], 422);
        }

        $item->status = 'rejected';
        $item->approved_at = now();
        $item->approved_by = $request->user()->id;
        $item->save();

        try {
            $item->user->notify(new \App\Notifications\RequestStatusUpdated('Cuti', 'rejected'));
        } catch (\Exception $e) {}

        return response()->json(['message' => 'Pengajuan berhasil ditolak', 'data' => $item]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:2000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photo' => 'nullable|file|mimes:jpeg,jpg,png,webp,heic,heif|max:20480',
        ]);

        $user = $request->user();
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        // Check for overlapping requests
        $overlap = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                      ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                      });
            })->exists();

        if ($overlap) {
            return response()->json([
                'message' => 'Anda sudah memiliki pengajuan cuti pada tanggal tersebut.',
            ], 422);
        }

        if ($validated['type'] === 'Cuti Tahunan') {
            $employment = $user->employment;
            if (!$employment || $employment->remaining_leave < $days) {
                return response()->json([
                    'message' => 'Sisa cuti tidak mencukupi. Sisa cuti Anda: ' . ($employment->remaining_leave ?? 0) . ' hari.',
                ], 422);
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('leave_requests', 'public');
        }

        $item = LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'notes' => $validated['notes'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'photo_path' => $photoPath,
            'status' => 'pending',
        ]);

        // Kirim Notifikasi ke Penyetuju
        $approver = \App\Models\DivisionApprover::where('division_name', $user->division_name)->first();
        if ($approver) {
            $approverUser = $approver->approver_id == $user->id ? $approver->director : $approver->approver;
            if ($approverUser) {
                try {
                    $approverUser->notify(new \App\Notifications\RequestSubmitted('Cuti', $user->name));
                } catch (\Exception $e) {
                    // Ignore notification errors to not break the request
                }
            }
        }

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dikirim ke ' . ($user->approver ?? 'Admin'),
            'data' => $item,
        ]);
    }
}

