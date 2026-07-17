<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Salary;
use App\Services\SalaryService;
use Carbon\Carbon;

class SalaryController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    public function index(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $query = Salary::with('user')
            ->where('month', $month)
            ->where('year', $year);

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

        // 1. Summary Metrics for Filtered Month
        $summary = [
            'total_net' => (clone $query)->sum('net_salary'),
            'total_overtime' => (clone $query)->sum(\DB::raw('overtime_pay + overtime_meal_pay')),
            'total_count' => (clone $query)->count(),
        ];

        // 2. History Data (Last 6 Months Trend)
        $historyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $hMonth = $date->month;
            $hYear = $date->year;
            $historyTrend[] = [
                'label' => $date->translatedFormat('M Y'),
                'total' => Salary::where('month', $hMonth)->where('year', $hYear)->sum('net_salary')
            ];
        }

        $salaries = $query->paginate(15)->withQueryString();

        return view('admin.salaries.index', compact('salaries', 'month', 'year', 'summary', 'historyTrend'));
    }

    public function calculate(Request $request)
    {
        $user = User::with('employment')->findOrFail($request->user_id);
        $month = $request->month;
        $year = $request->year;

        $results = $this->salaryService->calculate($user, $month, $year);

        // Adjust for manual tax if provided in preview
        if ($request->filled('income_tax') && $request->income_tax > 0) {
            $tax = (float) $request->income_tax;
            $results['income_tax'] = $tax;
            $results['tax_allowance'] = $tax;
            $results['total_earnings'] += $tax;
            $results['total_deductions'] += $tax;
            $results['net_salary'] = $results['total_earnings'] - $results['total_deductions'];
        }

        return response()->json($results);
    }

    public function create()
    {
        $users = User::all();
        return view('admin.salaries.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer',
            'year' => 'required|integer',
            'income_tax' => 'nullable|numeric',
        ]);

        $user = User::with('employment')->findOrFail($request->user_id);
        
        $data = $this->salaryService->calculate(
            $user, 
            $request->month, 
            $request->year
        );

        // Override income tax if manually provided
        if ($request->has('income_tax')) {
            $data['income_tax'] = $request->income_tax;
            $data['tax_allowance'] = $request->income_tax; 
            $data['total_earnings'] = $data['total_earnings'] - 0 + $request->income_tax; 
            $data['total_deductions'] = $data['total_deductions'] - 0 + $request->income_tax;
            $data['net_salary'] = $data['total_earnings'] - $data['total_deductions'];
        }

        // Prevent SQL error: remove overtime_hours since it's only for UI preview
        unset($data['overtime_hours']);

        Salary::updateOrCreate(
            ['user_id' => $data['user_id'], 'month' => $data['month'], 'year' => $data['year']],
            $data
        );

        return redirect()->route('admin.salaries.index')->with('success', 'Data gaji berhasil di-generate dan disimpan.');
    }

    public function publish(Request $request)
    {
        Salary::where('month', $request->month)
            ->where('year', $request->year)
            ->update(['status' => 'published']);
            
        return redirect()->back()->with('success', 'Semua slip gaji periode ini telah dipublikasikan.');
    }

    public function disburse(Request $request)
    {
        Salary::where('month', $request->month)
            ->where('year', $request->year)
            ->update(['status' => 'paid']);
            
        return redirect()->back()->with('success', 'Seluruh gaji periode ini telah berhasil dicairkan!');
    }

    public function generateAll(Request $request)
    {
        $month = $request->month;
        $year = $request->year;

        $users = User::where('is_admin', false)->get();
        $count = 0;

        foreach ($users as $user) {
            try {
                $data = $this->salaryService->calculate($user, $month, $year);
                
                // Remove UI-only fields
                unset($data['overtime_hours']);

                Salary::updateOrCreate(
                    ['user_id' => $user->id, 'month' => $month, 'year' => $year],
                    $data
                );
                $count++;
            } catch (\Exception $e) {
                // Skip if calculation fails for a specific user (e.g., missing employment data)
                continue;
            }
        }

        return redirect()->back()->with('success', "Berhasil men-generate {$count} data gaji untuk periode ini.");
    }

    public function show($id)
    {
        $salary = Salary::with(['user.employment'])->findOrFail($id);

        if (!auth()->user()->is_admin) {
            abort(403);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.salaries.slip_pdf', compact('salary'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Preview_SlipGaji.pdf');
    }

    public function downloadSlip($id)
    {
        $salary = Salary::with(['user.employment'])->findOrFail($id);

        // Security: only admin or the salary owner can download
        if (!auth()->user()->is_admin && auth()->id() !== $salary->user_id) {
            abort(403);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.salaries.slip_pdf', compact('salary'));
        $pdf->setPaper('a4', 'portrait');

        // Render first, then encrypt with employee's NIP as password
        // IMPORTANT: Do NOT use $salary->user->pin — it's a bcrypt hash, not the actual PIN.
        $pdf->render();
        $password = $salary->user->employee_id ?: '123456';
        $pdf->getDomPDF()->getCanvas()->get_cpdf()->setEncryption($password, $password, ['print']);

        $filename = 'SlipGaji_' . str_replace(' ', '_', $salary->user->name) . '_' . $salary->month . '_' . $salary->year . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
