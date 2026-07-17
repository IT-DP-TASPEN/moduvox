<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Salary;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));

        $salaries = Salary::with('user:id,employee_id,name')
            ->where('user_id', $user->id)
            ->whereIn('status', ['published', 'paid'])
            ->where('year', $year)
            ->when($month, function ($query, $month) {
                return $query->where('month', $month);
            })
            ->orderBy('month', 'desc')
            ->get();

        return response()->json($salaries);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $salary = Salary::where('user_id', $user->id)
            ->where('id', $id)
            ->whereIn('status', ['published', 'paid'])
            ->firstOrFail();

        return response()->json($salary);
    }

    public function downloadSlip(Request $request, $id)
    {
        $user = $request->user();
        $salary = Salary::with(['user.employment', 'user.profile', 'user.office'])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->whereIn('status', ['published', 'paid'])
            ->firstOrFail();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.salaries.slip_pdf', compact('salary'));
        $pdf->setPaper('a4', 'portrait');

        $pdf->render();
        
        // Use the plaintext PIN from the request as PDF password.
        // Fallback to employee_id (NIP) if no PIN provided, then '123456' as last resort.
        // IMPORTANT: Do NOT use $user->pin here — it's a bcrypt hash, not the actual PIN.
        $password = $request->input('password') ?: ($user->employee_id ?: '123456');
        
        $pdf->getDomPDF()->getCanvas()->get_cpdf()->setEncryption($password, $password, ['print']);

        $filename = 'SlipGaji_' . str_replace(' ', '_', $user->name) . '_' . $salary->month . '_' . $salary->year . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
