<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('user');

        $user = auth()->user();
        if ($user && !$user->is_admin) {
            $asApprover = \App\Models\DivisionApprover::where('approver_id', $user->id)->pluck('division_name');
            $asDirector = \App\Models\DivisionApprover::where('director_id', $user->id)->pluck('approver_id');

            $query->whereHas('user', function($q) use ($asApprover, $asDirector) {
                $q->whereIn('division_name', $asApprover)
                  ->orWhereIn('id', $asDirector);
            });
        }

        // Filter Search (Nama/NIP)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                });
            });
        }

        // Filter Tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter Month & Year
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            } else {
                $query->whereYear('created_at', date('Y'));
            }
        }

        // Filter Tipe
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $attendances = $query->latest()->paginate(15)->withQueryString();
        
        return view('admin.attendances.index', compact('attendances'));
    }
}
