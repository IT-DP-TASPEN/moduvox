<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\KpiRecord;
use App\Models\DivisionApprover;
use Carbon\Carbon;

class KpiController extends Controller
{
    /**
     * Display a list of staff in the user's division for KPI input.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        // Get divisions where this user is an approver or director
        $managedDivisions = DivisionApprover::where('approver_id', $user->id)
            ->orWhere('director_id', $user->id)
            ->pluck('division_name');

        if ($managedDivisions->isEmpty() && !$user->is_admin) {
            return abort(403, 'Anda tidak memiliki wewenang untuk mengisi KPI.');
        }

        $query = User::query();

        if (!$user->is_admin) {
            $query->whereIn('division_name', $managedDivisions);
        }

        // Filter Search (Nama/NIP)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $staff = $query->with(['employment'])->paginate(15)->withQueryString();
        
        $month = $request->query('month', now()->subMonth()->month);
        $year = $request->query('year', now()->subMonth()->year);

        // Load existing KPI for this month/year for the current staff list
        $staffIds = $staff->pluck('id');
        $kpiMap = KpiRecord::where('month', $month)
            ->where('year', $year)
            ->whereIn('user_id', $staffIds)
            ->get()
            ->keyBy('user_id');

        return view('admin.kpi.index', compact('staff', 'managedDivisions', 'month', 'year', 'kpiMap'));
    }

    /**
     * Store or update KPI records.
     */
    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
            'kpi' => 'required|array',
            'kpi.*.score' => 'nullable|numeric|between:0,100',
            'kpi.*.notes' => 'nullable|string',
        ]);

        foreach ($request->kpi as $userId => $data) {
            if ($data['score'] === null) continue;

            $grade = $this->calculateGrade($data['score']);

            KpiRecord::updateOrCreate(
                [
                    'user_id' => $userId,
                    'month' => $request->month,
                    'year' => $request->year,
                ],
                [
                    'score' => $data['score'],
                    'grade' => $grade,
                    'notes' => $data['notes'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('success', 'KPI berhasil disimpan.');
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
