<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiRecord;
use App\Models\User;
use App\Models\DivisionApprover;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KpiController extends Controller
{
    /**
     * Get KPI data for the current user and their staff.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->query('month', now()->subMonth()->month);
        $year = $request->query('year', now()->subMonth()->year);

        // Get active indicators
        $indicators = \App\Models\KpiIndicator::where('is_active', true)->orderBy('sort_order')->get();

        // 1. Get user's own KPI
        $ownKpi = KpiRecord::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        // 2. Get managed divisions
        $asApprover = DivisionApprover::where('approver_id', $user->id)->pluck('division_name');
        $asDirector = DivisionApprover::where('director_id', $user->id)->pluck('approver_id');

        // 3. Get staff and their KPI status
        $staff = User::where(function($q) use ($asApprover, $asDirector, $user) {
                $q->whereIn('division_name', $asApprover)
                  ->where('id', '!=', $user->id);
            })
            ->orWhereIn('id', $asDirector)
            ->with(['employment'])
            ->get();

        $staffIds = $staff->pluck('id');
        $kpiMap = KpiRecord::where('month', $month)
            ->where('year', $year)
            ->whereIn('user_id', $staffIds)
            ->get()
            ->keyBy('user_id');

        $staffList = $staff->map(function($s) use ($kpiMap) {
            $kpi = $kpiMap->get($s->id);
            return [
                'id' => $s->id,
                'name' => $s->name,
                'employee_id' => $s->employee_id,
                'division' => $s->division_name,
                'is_rated' => !is_null($kpi),
                'kpi' => $kpi,
            ];
        });

        return response()->json([
            'period' => [
                'month' => (int)$month,
                'year' => (int)$year,
                'formatted' => Carbon::create(null, $month)->translatedFormat('F') . ' ' . $year
            ],
            'indicators' => $indicators,
            'own_kpi' => $ownKpi,
            'staff_list' => $staffList,
            'is_approver' => $staff->isNotEmpty()
        ]);
    }

    /**
     * Store KPI for a staff member.
     */
    public function store(Request $request)
    {
        $activeIndicators = \App\Models\KpiIndicator::where('is_active', true)->get();
        
        $rules = [
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
            'indicators' => 'required|array',
            'notes' => 'nullable|string',
        ];

        foreach ($activeIndicators as $indicator) {
            $rules["indicators.{$indicator->slug}"] = 'required|numeric|between:1,10';
        }

        $validated = $request->validate($rules);

        // Calculate total score (out of 100)
        // Max sum = count * 10.
        $sum = 0;
        foreach ($activeIndicators as $indicator) {
            $sum += $validated['indicators'][$indicator->slug];
        }
        
        $maxSum = $activeIndicators->count() * 10;
        $score = $maxSum > 0 ? ($sum / $maxSum) * 100 : 0;

        $grade = $this->calculateGrade($score);

        $kpi = KpiRecord::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'month' => $validated['month'],
                'year' => $validated['year'],
            ],
            [
                'indicators' => $validated['indicators'],
                'score' => $score,
                'grade' => $grade,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'KPI berhasil disimpan',
            'data' => $kpi
        ]);
    }

    private function calculateGrade($score)
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'E';
    }
}
