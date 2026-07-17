<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceSummaryService;

class AttendanceController extends Controller
{
    protected $summaryService;

    public function __construct(AttendanceSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:masuk,keluar',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            // Camera output can be HEIC/HEIF on some devices; accept common formats and allow larger sizes.
            'photo' => 'nullable|file|mimes:jpeg,jpg,png,webp,heic,heif|max:20480',
        ]);

        $user = $request->user();
        
        // Prevent duplicate attendance for the same day
        $today = now()->timezone('Asia/Jakarta')->startOfDay();
        $exists = \App\Models\Attendance::where('user_id', $user->id)
            ->where('type', $request->type)
            ->where('created_at', '>=', $today)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Anda sudah melakukan absen ' . $request->type . ' hari ini.',
            ], 422);
        }

        $offices = $user->all_offices;
        $withinRadius = false;
        $closestDistance = null;
        $requiredRadius = 0;

        if ($offices->isEmpty()) {
            $withinRadius = true; // No office assigned, maybe allow? Or skip check.
        } else {
            foreach ($offices as $office) {
                // Hitung jarak (Haversine Formula)
                $earthRadius = 6371000; // dalam meter
                $lat1 = deg2rad($office->latitude);
                $lon1 = deg2rad($office->longitude);
                $lat2 = deg2rad($request->latitude);
                $lon2 = deg2rad($request->longitude);

                $dlat = $lat2 - $lat1;
                $dlon = $lon2 - $lon1;

                $a = sin($dlat / 2) * sin($dlat / 2) +
                     cos($lat1) * cos($lat2) *
                     sin($dlon / 2) * sin($dlon / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance = $earthRadius * $c;

                if ($distance <= $office->radius) {
                    $withinRadius = true;
                    break;
                }
                
                if ($closestDistance === null || $distance < $closestDistance) {
                    $closestDistance = $distance;
                    $requiredRadius = $office->radius;
                }
            }
        }

        if (!$withinRadius) {
            return response()->json([
                'message' => 'Anda berada di luar radius kantor (' . round($closestDistance) . 'm). Jarak maksimum adalah ' . $requiredRadius . 'm. Silakan mendekat ke salah satu kantor yang terdaftar.',
            ], 422);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendances', 'public');
        }

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'photo_path' => $photoPath,
            'status' => 'success',
        ]);

        // Update daily summary
        $this->summaryService->updateDailySummary($user->id, $attendance->created_at);

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => array_merge($attendance->toArray(), [
                'created_at' => optional($attendance->created_at)->timezone('Asia/Jakarta')->toIso8601String(),
                'updated_at' => optional($attendance->updated_at)->timezone('Asia/Jakarta')->toIso8601String(),
            ]),
        ]);
    }

    public function history(Request $request)
    {
        $history = \App\Models\Attendance::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        $history->getCollection()->transform(function ($attendance) {
            return array_merge($attendance->toArray(), [
                'created_at' => optional($attendance->created_at)->timezone('Asia/Jakarta')->toIso8601String(),
                'updated_at' => optional($attendance->updated_at)->timezone('Asia/Jakarta')->toIso8601String(),
            ]);
        });

        return response()->json($history);
    }

    public function recap(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $user = $request->user();
        $userId = $request->user_id ?? $user->id;

        if ($userId != $user->id && !$user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();

        $summaries = \App\Models\AttendanceSummary::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalWorkingDays = 0;
        $tempDate = clone $startDate;
        while ($tempDate <= $endDate) {
            if (!$tempDate->isWeekend()) $totalWorkingDays++;
            $tempDate->addDay();
        }

        // Total Kehadiran: Hadir + Cuti + Izin + Tugas Luar
        $totalAttendance = $summaries->filter(function($s) {
            return $s->is_attendance || $s->leave_days > 0 || $s->permit_count > 0 || $s->outside_duty_count > 0;
        })->count();
        $totalDurationMinutes = $summaries->sum('duration_minutes');
        $totalLateMinutes = $summaries->sum('late_minutes');
        $totalEarlyDepartureMinutes = $summaries->sum('early_departure_minutes');
        $totalLeaveDays = $summaries->sum('leave_days');
        $totalSpecialLeaveDays = $summaries->where('leave_type', 'special')->sum('leave_days');
        $totalSickLeaveDays = $summaries->where('leave_type', 'sick')->sum('leave_days');
        $totalOutsideDuty = $summaries->sum('outside_duty_count');
        $totalOvertimeMinutes = $summaries->sum('overtime_minutes');
        $totalPermit = $summaries->sum('permit_count');
        $absentDays = $summaries->where('status', 'alpa')->count();

        return response()->json([
            'total_working_days' => $totalWorkingDays . ' hari',
            'total_attendance' => $totalAttendance . ' hari',
            'out_of_schedule' => '0 hari',
            'attendance_duration' => $this->formatMinutes($totalDurationMinutes),
            'attendance_hours' => $this->formatMinutes($totalDurationMinutes),
            'leave' => $totalLeaveDays . ' hari',
            'special_leave' => $totalSpecialLeaveDays . ' hari',
            'sick_leave' => $totalSickLeaveDays . ' hari',
            'permit' => $totalPermit . ' kali',
            'outside_duty' => $totalOutsideDuty . ' kali',
            'outside_duty_holiday' => '0 hari',
            'absent' => $absentDays . ' hari',
            'overtime' => $this->formatMinutes($totalOvertimeMinutes),
            'late' => $this->formatMinutes($totalLateMinutes),
            'early_departure' => $this->formatMinutes($totalEarlyDepartureMinutes),
            'details' => $summaries->values()->toArray(), // Pastikan dalam bentuk array list
        ]);
    }

    private function formatMinutes($minutes)
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        if ($hours > 0) {
            return "{$hours} jam {$remainingMinutes} menit";
        }
        return "{$remainingMinutes} menit";
    }
}
