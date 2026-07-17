<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\PositionAllowance;
use App\Models\Division;
use App\Models\Office;
use App\Models\OfficeApprover;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function index()
    {
        // Data for Left Panel (Division Tree)
        $divisions = Division::withCount('positions')->orderBy('name')->get();
        
        // Data for Main Panel (Initial load - all)
        $positions = Position::with(['division', 'allowances' => function($q) {
            $q->orderBy('effective_date', 'desc');
        }])->orderBy('name')->get();

        // Data for Contextual Selects
        $offices = Office::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        
        // Stats for Filter Bar
        $stats = [
            'total_positions' => Position::count(),
            'total_pusat' => Position::where('category', 'PUSAT')->count(),
            'total_cabang' => Position::where('category', 'CABANG')->count(),
        ];

        return view('admin.positions.index', compact('divisions', 'positions', 'offices', 'users', 'stats'));
    }

    /**
     * AJAX: Get Full Detail for Right Panel
     */
    public function getDetail($id)
    {
        $position = Position::with(['division', 'allowances' => function($q) {
            $q->orderBy('effective_date', 'desc');
        }])->findOrFail($id);

        // Get count of employees using this position
        $usageCount = 0;
        try {
            $usageCount = \App\Models\User::where('position_id', $id)->count();
        } catch (\Exception $e) { }

        // Get Approval Rules
        $approvalRules = null;
        if ($position->category === 'PUSAT' && $position->division_id) {
            $headOffice = Office::where('name', 'like', '%PUSAT%')->first();
            if ($headOffice) {
                $approvalRules = OfficeApprover::where('office_id', $headOffice->id)
                    ->where('division_id', $position->division_id)
                    ->with(['approver', 'director'])
                    ->first();
            }
        }

        return response()->json([
            'position' => $position,
            'usage_count' => $usageCount,
            'approval_rules' => $approvalRules,
            'allowance' => $position->currentAllowance()
        ]);
    }

    /**
     * AJAX: Quick Inline Update for Allowance
     */
    public function quickUpdateAllowance(Request $request, $id)
    {
        $request->validate(['amount' => 'required|numeric|min:0']);
        
        PositionAllowance::updateOrCreate(
            ['position_id' => $id, 'effective_date' => now()->toDateString()],
            ['amount' => $request->amount]
        );

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Save Full Config from Right Panel
     */
    public function saveFullConfig(Request $request, $id)
    {
        $position = Position::findOrFail($id);
        
        DB::transaction(function() use ($request, $position) {
            // Update Basic Info
            $position->update([
                'name' => $request->name,
                'division_id' => $request->division_id,
                'category' => $request->category,
            ]);

            // Update Allowance
            PositionAllowance::updateOrCreate(
                ['position_id' => $position->id, 'effective_date' => now()->toDateString()],
                ['amount' => preg_replace('/[^0-9]/', '', $request->amount ?? 0)]
            );

            // If PUSAT, update functional approval if provided
            if ($request->category === 'PUSAT' && $request->division_id && $request->has('approver_id')) {
                $headOffice = Office::where('name', 'like', '%PUSAT%')->first();
                if ($headOffice) {
                    OfficeApprover::updateOrCreate(
                        ['office_id' => $headOffice->id, 'division_id' => $request->division_id],
                        ['approver_id' => $request->approver_id, 'director_id' => $request->director_id]
                    );
                }
            }
        });

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:positions,name',
            'division_id' => 'required|exists:divisions,id',
            'category' => 'required|in:PUSAT,CABANG',
            'amount' => 'required|numeric|min:0',
        ]);

        $position = Position::create($request->only('name', 'division_id', 'category'));
        PositionAllowance::create([
            'position_id' => $position->id,
            'amount' => $request->amount,
            'effective_date' => now()->toDateString()
        ]);

        return redirect()->back()->with('success', 'Jabatan berhasil dibuat!');
    }

    public function destroy($id)
    {
        Position::destroy($id);
        return redirect()->back()->with('success', 'Jabatan berhasil dihapus.');
    }
    
    public function storeDivision(Request $request)
    {
        $request->validate(['name' => 'required|unique:divisions,name']);
        
        $code = strtoupper(str_replace(' ', '-', $request->name));
        
        Division::create([
            'name' => $request->name,
            'code' => $code
        ]);

        return redirect()->back()->with('success', 'Divisi berhasil dibuat.');
    }

    public function updateDivision(Request $request, $id)
    {
        $division = Division::findOrFail($id);
        $request->validate(['name' => 'required|unique:divisions,name,' . $id]);
        $division->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Nama divisi berhasil diubah!');
    }

    public function destroyDivision($id)
    {
        Position::where('division_id', $id)->update(['division_id' => null]);
        Division::destroy($id);
        return redirect()->back()->with('success', 'Divisi berhasil dihapus.');
    }

    public function updateOfficeApprover(Request $request, $id)
    {
        $office = Office::findOrFail($id);
        OfficeApprover::updateOrCreate(
            ['office_id' => $office->id, 'division_id' => $request->division_id],
            ['approver_id' => $request->approver_id, 'director_id' => $request->director_id]
        );
        return redirect()->back()->with('success', 'Jalur approval berhasil diperbarui!');
    }

    public function destroyOfficeApprover($id)
    {
        OfficeApprover::destroy($id);
        return redirect()->back()->with('success', 'Setting approval berhasil dihapus.');
    }
}
