<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filterMonth = $request->query('month', now()->month);
        $filterYear = $request->query('year', now()->year);
        $targetDateCarbon = \Carbon\Carbon::create($filterYear, $filterMonth, 1);
        $isCurrentMonth = ($filterMonth == now()->month && $filterYear == now()->year);
        $targetDate = $isCurrentMonth ? now() : $targetDateCarbon->copy()->endOfMonth();
        $targetDateString = $targetDate->toDateString();

        $totalUsers = User::where('is_admin', false)->count();
        $totalAttendanceToday = Attendance::whereDate('created_at', $targetDateString)
            ->where('type', 'masuk')
            ->count();
        
        // 1. Executive Summary (Top Section)
        $newHiresMonth = User::whereMonth('join_date', $filterMonth)
            ->whereYear('join_date', $filterYear)
            ->count();
        $resignMonth = User::onlyTrashed()
            ->whereMonth('deleted_at', $filterMonth)
            ->whereYear('deleted_at', $filterYear)
            ->count();
        $turnoverRate = $totalUsers > 0 ? round(($resignMonth / ($totalUsers + $resignMonth)) * 100, 2) : 0;
        
        // Summary Cuti/Izin/Tugas Luar Hari Ini (Approved)
        $onLeaveToday = \App\Models\LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $targetDate)
            ->whereDate('end_date', '>=', $targetDate)
            ->count();
        $onPermitToday = \App\Models\PermitRequest::where('status', 'approved')
            ->whereDate('created_at', $targetDateString)
            ->count();
        $onDutyToday = \App\Models\OutsideDutyRequest::where('status', 'approved')
            ->whereDate('created_at', $targetDateString)
            ->count();

        // 2. Absensi & Kehadiran Insight
        // Tren Kehadiran 30 Hari Terakhir (single query instead of 30)
        $startDate = $targetDate->copy()->subDays(29)->startOfDay();
        $trendData = Attendance::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $targetDate->copy()->endOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $attendanceTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $targetDate->copy()->subDays($i);
            $attendanceTrend[] = [
                'date' => $date->format('d M'),
                'count' => $trendData[$date->toDateString()] ?? 0
            ];
        }

        $isSqlite = \DB::connection()->getDriverName() === 'sqlite';
        $timeToSec = $isSqlite ? "(cast(strftime('%H', created_at) as integer) * 3600 + cast(strftime('%M', created_at) as integer) * 60 + cast(strftime('%S', created_at) as integer))" : "TIME_TO_SEC(created_at)";
        $timeCol = $isSqlite ? "strftime('%H:%M:%S', created_at)" : "TIME(created_at)";
        $dayCol = $isSqlite ? "cast(strftime('%d', birth_date) as integer)" : "DAY(birth_date)";

        // Rata-rata Check-in/out (Hari ini)
        $avgCheckIn = Attendance::whereDate('created_at', $targetDateString)
            ->where('type', 'masuk')
            ->avg(\DB::raw($timeToSec));
        $avgCheckOut = Attendance::whereDate('created_at', $targetDateString)
            ->where('type', 'keluar')
            ->avg(\DB::raw($timeToSec));

        $avgCheckIn = $avgCheckIn ? gmdate("H:i", $avgCheckIn) : '00:00';
        $avgCheckOut = $avgCheckOut ? gmdate("H:i", $avgCheckOut) : '00:00';

        // Top 5 Terlambat (Bulan ini, telat = > 08:00)
        $topLatecomers = Attendance::where('type', 'masuk')
            ->whereMonth('created_at', $filterMonth)
            ->whereYear('created_at', $filterYear)
            ->where(\DB::raw($timeCol), '>', '08:00:00')
            ->select('user_id', \DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->with('user')
            ->take(5)
            ->get();

        // 3. Cuti & Izin Visuals
        $leaveTypes = \App\Models\LeaveRequest::whereMonth('created_at', $filterMonth)
            ->whereYear('created_at', $filterYear)
            ->select('type', \DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();
        
        // 4. Pending Approvals (Operational Alert)
        $pendingApprovals = [
            'leave' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
            'permit' => \App\Models\PermitRequest::where('status', 'pending')->count(),
            'overtime' => \App\Models\OvertimeRequest::where('status', 'pending')->count(),
            'outside' => \App\Models\OutsideDutyRequest::where('status', 'pending')->count(),
        ];
        $totalPending = array_sum($pendingApprovals);

        // 5. Distribusi Karyawan
        $divisionStats = User::where('is_admin', false)
            ->select('division_name', \DB::raw('count(*) as total'))
            ->groupBy('division_name')
            ->get();

        $officeStats = \App\Models\Office::withCount(['users' => function($q) {
            $q->where('is_admin', false);
        }])->get();

        // 6. Payroll Insight Expanded
        $payrollQuery = \App\Models\Salary::where('month', $filterMonth)
            ->where('year', $filterYear);

        $totalPayroll = $payrollQuery->sum('net_salary');
        
        $totalOvertimePay = $payrollQuery->sum(\DB::raw('overtime_pay + overtime_meal_pay'));

        $avgSalaryPerDivision = clone $payrollQuery;
        $avgSalaryPerDivision = $avgSalaryPerDivision->join('users', 'salaries.user_id', '=', 'users.id')
            ->select('users.division_name', \DB::raw('AVG(net_salary) as avg_salary'))
            ->groupBy('users.division_name')
            ->orderBy('avg_salary', 'desc')
            ->get();

        // 7. Kontrak Habis (Operational Alert)
        $expiringContracts = \App\Models\EmploymentDetail::whereBetween('contract_end_date', [$targetDate, $targetDate->copy()->addMonths(3)])
            ->with('user')
            ->get();

        // 8. Karyawan Berulang Tahun (Engagement)
        $birthdaysThisMonth = User::where('is_admin', false)
            ->whereMonth('birth_date', $filterMonth)
            ->orderByRaw("$dayCol ASC")
            ->get();

        // 9. Recent Activity (Timeline)
        $recentActivities = Attendance::with('user')
            ->whereMonth('created_at', $filterMonth)
            ->whereYear('created_at', $filterYear)
            ->latest()
            ->take(6)
            ->get();

        // Stats Gender & Umur (Still used for charts)
        $genderStats = [
            'L' => User::where('is_admin', false)->where('gender', 'L')->count(),
            'P' => User::where('is_admin', false)->where('gender', 'P')->count(),
        ];

        // 8. Leaderboard / Behavioral Insight
        // Top 5 Early Birds (Rata-rata check-in paling awal bulan ini)
        $earlyBirds = Attendance::where('type', 'masuk')
            ->whereMonth('created_at', $filterMonth)
            ->whereYear('created_at', $filterYear)
            ->select('user_id', \DB::raw("AVG($timeToSec) as avg_time"))
            ->groupBy('user_id')
            ->orderBy('avg_time', 'asc')
            ->with('user')
            ->take(5)
            ->get();

        // Divisi Paling Rajin (Persentase kehadiran tertinggi hari ini)
        $divisionAttendance = User::where('is_admin', false)
            ->select('division_name', \DB::raw('count(*) as total_emp'))
            ->groupBy('division_name')
            ->get()
            ->map(function($div) use ($targetDateString) {
                $present = Attendance::whereDate('created_at', $targetDateString)
                    ->whereHas('user', function($q) use ($div) {
                        $q->where('division_name', $div->division_name);
                    })->count();
                $div->rate = $div->total_emp > 0 ? ($present / $div->total_emp) * 100 : 0;
                return $div;
            })->sortByDesc('rate')->first();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalAttendanceToday', 'newHiresMonth', 'resignMonth', 'turnoverRate',
            'onLeaveToday', 'onPermitToday', 'onDutyToday',
            'attendanceTrend', 'avgCheckIn', 'avgCheckOut', 'topLatecomers',
            'leaveTypes', 'pendingApprovals', 'totalPending',
            'divisionStats', 'officeStats', 'totalPayroll', 'totalOvertimePay', 'avgSalaryPerDivision',
            'expiringContracts', 'birthdaysThisMonth', 'recentActivities',
            'genderStats', 'earlyBirds', 'divisionAttendance',
            'filterMonth', 'filterYear'
        ));
    }
}
