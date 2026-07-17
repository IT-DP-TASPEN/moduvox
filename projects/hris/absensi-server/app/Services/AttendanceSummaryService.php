<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSummary;
use App\Models\LeaveRequest;
use App\Models\OutsideDutyRequest;
use App\Models\OvertimeRequest;
use App\Models\PermitRequest;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSummaryService
{
    public function updateDailySummary($userId, $date)
    {
        $carbonDate = Carbon::parse($date)->startOfDay();
        $isWorkingDay = !$carbonDate->isWeekend();

        $summary = AttendanceSummary::firstOrNew([
            'user_id' => $userId,
            'date' => $carbonDate->format('Y-m-d'),
        ]);

        $summary->is_working_day = $isWorkingDay;

        // Attendance logic
        $attendances = Attendance::where('user_id', $userId)
            ->whereDate('created_at', $carbonDate)
            ->where('status', 'success')
            ->get();

        $masuk = $attendances->where('type', 'masuk')->first();
        $keluar = $attendances->where('type', 'keluar')->first();

        $summary->is_attendance = $masuk != null;
        $summary->check_in = $masuk ? $masuk->created_at->format('H:i:s') : null;
        $summary->check_out = $keluar ? $keluar->created_at->format('H:i:s') : null;

        if ($masuk && $keluar) {
            $summary->duration_minutes = $masuk->created_at->diffInMinutes($keluar->created_at);
        } else {
            $summary->duration_minutes = 0;
        }

        // Late & Early Departure
        if ($masuk) {
            $scheduleMasuk = Carbon::parse($carbonDate->format('Y-m-d') . ' 08:00:00');
            $diff = $scheduleMasuk->diffInMinutes($masuk->created_at, false); // false to get negative if early
            $summary->late_minutes = $diff > 0 ? $diff : 0;
        }

        if ($keluar) {
            $scheduleKeluar = Carbon::parse($carbonDate->format('Y-m-d') . ' 17:00:00');
            $summary->early_departure_minutes = $keluar->created_at->lt($scheduleKeluar) 
                ? $scheduleKeluar->diffInMinutes($keluar->created_at) 
                : 0;
        }

        // Leave Requests
        $leave = LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $carbonDate)
            ->whereDate('end_date', '>=', $carbonDate)
            ->first();

        if ($leave) {
            $summary->leave_type = $leave->type;
            $summary->leave_days = 1;
            $summary->status = 'cuti';
        } else {
            $summary->leave_type = null;
            $summary->leave_days = 0;
        }

        // Outside Duty
        $outsideDuty = OutsideDutyRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_at', $carbonDate)
            ->exists();
        $summary->outside_duty_count = $outsideDuty ? 1 : 0;

        // Overtime from OvertimeRequest
        $overtimes = OvertimeRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_at', $carbonDate)
            ->get();
        
        $summary->overtime_minutes = 0;
        foreach ($overtimes as $ot) {
            if ($ot->end_at) {
                $summary->overtime_minutes += $ot->start_at->diffInMinutes($ot->end_at);
            }
        }

        // Add overtime from OutsideDutyRequest
        $outsideDutyOvertime = OutsideDutyRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_at', $carbonDate)
            ->sum('overtime_minutes');
        
        $summary->overtime_minutes += $outsideDutyOvertime;

        // Permits
        $permits = PermitRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('requested_at', $carbonDate)
            ->count();
        $summary->permit_count = $permits;

        // Final Status
        // Final Status Priority: Leave > Outside Duty (WFH) > Attendance > Working Day (Alpa) > Weekend (Libur)
        if ($summary->leave_days > 0) {
            $summary->status = 'cuti';
        } elseif ($summary->outside_duty_count > 0) {
            $summary->status = 'tugas_luar';
            // If it's WFH/Outside Duty on weekend, we consider it a sort of working day for attendance count
        } elseif ($summary->is_attendance) {
            $summary->status = 'hadir';
        } elseif ($isWorkingDay) {
            $summary->status = 'alpa';
        } else {
            $summary->status = 'libur';
            $summary->is_working_day = false; // Double check
        }

        $summary->save();
        return $summary;
    }
}
