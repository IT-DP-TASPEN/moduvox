<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DivisionApprover;
use App\Models\User;

class DivisionApproverController extends Controller
{
    public function index()
    {
        $approvers = DivisionApprover::with(['approver', 'director'])->get();
        return view('admin.division_approvers.index', compact('approvers'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        $divisions = \App\Models\Division::orderBy('name')->get();
        return view('admin.division_approvers.create', compact('users', 'divisions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division_name' => 'required|string|unique:division_approvers,division_name',
            'approver_id' => 'nullable|exists:users,id',
            'director_id' => 'nullable|exists:users,id',
        ]);

        DivisionApprover::create($validated);

        return redirect()->route('admin.division-approvers.index')->with('success', 'Setting Approval berhasil ditambahkan.');
    }

    public function edit(DivisionApprover $divisionApprover)
    {
        $users = User::orderBy('name')->get();
        $divisions = \App\Models\Division::orderBy('name')->get();
        return view('admin.division_approvers.edit', compact('divisionApprover', 'users', 'divisions'));
    }

    public function update(Request $request, DivisionApprover $divisionApprover)
    {
        $validated = $request->validate([
            'division_name' => 'required|string|unique:division_approvers,division_name,' . $divisionApprover->id,
            'approver_id' => 'nullable|exists:users,id',
            'director_id' => 'nullable|exists:users,id',
        ]);

        $divisionApprover->update($validated);

        return redirect()->route('admin.division-approvers.index')->with('success', 'Setting Approval berhasil diperbarui.');
    }

    public function destroy(DivisionApprover $divisionApprover)
    {
        $divisionApprover->delete();
        return redirect()->route('admin.division-approvers.index')->with('success', 'Setting Approval berhasil dihapus.');
    }
}
