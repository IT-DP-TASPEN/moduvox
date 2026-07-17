<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\User::withTrashed()->with('office')->where('is_admin', false);

        // Filter Search (Nama/NIP)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            if ($request->status === 'Nonaktif') {
                $query->onlyTrashed();
            } else {
                $query->withoutTrashed()->where('employment_status', $request->status);
            }
        }

        if (!$request->filled('status')) {
            $query->withoutTrashed();
        }

        // Filter Gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        
        $allUsers = \App\Models\User::withTrashed()->where('is_admin', false)->get();
        $stats = [
            'total' => $allUsers->count(),
            'inactive' => $allUsers->filter(fn($u) => $u->trashed())->count(),
            'male' => $allUsers->where('gender', 'L')->count(),
            'female' => $allUsers->where('gender', 'P')->count(),
            'ojt' => $allUsers->where('employment_status', 'OJT')->count(),
            'kontrak' => $allUsers->where('employment_status', 'Kontrak')->count(),
            'tetap' => $allUsers->where('employment_status', 'Tetap')->count(),
        ];

        return view('admin.employees.index', compact('users', 'stats'));
    }

    public function create()
    {
        $offices = \App\Models\Office::all();
        $divisions = \App\Models\Division::orderBy('name')->get();
        
        $skgOptions = \App\Models\GapokMaster::select('skg')->distinct()->orderBy('skg')->pluck('skg');
        $gradeOptions = \App\Models\GapokMaster::select('grade')->distinct()->orderBy('grade')->pluck('grade');
        $positionOptions = \App\Models\Position::orderBy('name')->get();

        return view('admin.employees.create', compact('offices', 'divisions', 'skgOptions', 'gradeOptions', 'positionOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'required|string|unique:users,employee_id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'employee_id' => $request->employee_id,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'office_id' => $request->office_id,
            'division_name' => $request->division_name,
            'employment_status' => $request->employment_status,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
        ]);

        // Create initial profile and employment
        $profileData = $request->input('profile', []);
        $employmentData = $request->input('employment', []);

        // 1. Fetch Basic Salary from GapokMaster
        $gapok = \App\Models\GapokMaster::where('skg', $employmentData['skg'] ?? 0)
            ->where('grade', $employmentData['grade'] ?? '')
            ->first();
        $employmentData['basic_salary'] = $gapok ? $gapok->amount : 0;

        // 2. Fetch Allowances from New Position Master
        if (!empty($employmentData['position_id'])) {
            $pos = \App\Models\Position::find($employmentData['position_id']);
            if ($pos) {
                $employmentData['position'] = $pos->name; // Legacy string sync
                $allowance = $pos->currentAllowance();
                $employmentData['position_allowance'] = $allowance ? $allowance->amount : 0;
            }
        }

        // 3. Sanitize remaining numeric fields
        foreach (['leave_quota'] as $field) {
            if (empty($employmentData[$field])) $employmentData[$field] = 0;
        }
        
        // For new employees, remaining leave equals leave quota
        if (!isset($employmentData['remaining_leave']) || $employmentData['remaining_leave'] === '') {
            $employmentData['remaining_leave'] = $employmentData['leave_quota'];
        }

        $user->profile()->create($profileData);
        $user->employment()->create($employmentData);

        // Sync additional offices
        if ($request->has('additional_offices')) {
            $user->additionalOffices()->sync($request->additional_offices);
        }

        return redirect()->route('admin.employees.show', $user)->with('success', 'Karyawan baru berhasil ditambahkan!');
    }

    public function show($id)
    {
        $user = \App\Models\User::withTrashed()
            ->with([
                'office', 'mutations', 'warnings', 'files', 'attendances', 'profile', 'employment.positionMaster',
                'leaveRequests', 'permitRequests', 'overtimeRequests', 'outsideDutyRequests', 'histories'
            ])
            ->findOrFail($id);
        return view('admin.employees.show', compact('user'));
    }

    public function edit($id)
    {
        $user = \App\Models\User::withTrashed()->with('employment')->findOrFail($id);
        $offices = \App\Models\Office::all();
        $divisions = \App\Models\Division::orderBy('name')->get();
        
        $skgOptions = \App\Models\GapokMaster::select('skg')->distinct()->orderBy('skg')->pluck('skg');
        $gradeOptions = \App\Models\GapokMaster::select('grade')->distinct()->orderBy('grade')->pluck('grade');
        $positionOptions = \App\Models\Position::orderBy('name')->get();

        return view('admin.employees.edit', compact('user', 'offices', 'divisions', 'skgOptions', 'gradeOptions', 'positionOptions'));
    }

    public function update(Request $request, $id)
    {
        $user = \App\Models\User::withTrashed()->with('employment')->findOrFail($id);
        $oldEmployment = $user->employment ? $user->employment->toArray() : [];
        $oldUser = $user->toArray();

        // 1. Update Core User Data
        $userData = $request->only([
            'name', 'employee_id', 'email', 'phone', 'office_id', 'division_name', 'employment_status', 'birth_date', 'gender'
        ]);
        
        if ($request->has('employment.join_date')) {
            $userData['join_date'] = $request->input('employment.join_date');
        }

        $user->update($userData);

        // 2. Update/Create Profile Data
        if ($request->has('profile')) {
            $profile = $user->profile ?: new \App\Models\EmployeeProfile();
            $profile->user_id = $user->id;
            $profile->fill($request->input('profile'));
            $profile->save();
        }

        // 3. Update/Create Employment Data
        if ($request->has('employment')) {
            $employment = $user->employment ?: new \App\Models\EmploymentDetail();
            $employment->user_id = $user->id;
            
            $employmentData = $request->input('employment');

            // 1. Fetch Basic Salary from GapokMaster
            $gapok = \App\Models\GapokMaster::where('skg', $employmentData['skg'] ?? 0)
                ->where('grade', $employmentData['grade'] ?? '')
                ->first();
            $employmentData['basic_salary'] = $gapok ? $gapok->amount : 0;

            // 2. Fetch Allowances from New Position Master
            if (!empty($employmentData['position_id'])) {
                $pos = \App\Models\Position::find($employmentData['position_id']);
                if ($pos) {
                    $employmentData['position'] = $pos->name; // Legacy string sync
                    
                    // Only update base allowance if there is no manual override
                    if (empty($employmentData['allowance_override'])) {
                        $allowance = $pos->currentAllowance();
                        $employmentData['position_allowance'] = $allowance ? $allowance->amount : 0;
                    }
                }
            }

            // 3. Sanitize numeric fields
            foreach (['leave_quota', 'remaining_leave'] as $field) {
                if (!isset($employmentData[$field]) || $employmentData[$field] === '' || $employmentData[$field] === null) {
                    $employmentData[$field] = 0;
                }
            }

            $employment->fill($employmentData);
            $employment->save();

            // Sync additional offices
            $user->additionalOffices()->sync($request->input('additional_offices', []));

            // 4. LOG HISTORY CHANGES
            $fieldsToTrack = [
                'skg' => 'SKG',
                'grade' => 'Golongan/Tingkat',
                'position' => 'Jabatan',
                'department' => 'Departemen'
            ];

            foreach ($fieldsToTrack as $field => $label) {
                $oldVal = $oldEmployment[$field] ?? null;
                $newVal = $employment->$field;

                if ($oldVal != $newVal && !empty($oldVal)) {
                    $type = 'Promotion';
                    if ($field == 'skg' || $field == 'grade') $type = 'Promotion';
                    if ($field == 'department') $type = 'Rotation';

                    \App\Models\EmploymentHistory::create([
                        'user_id' => $user->id,
                        'type' => $type,
                        'field' => $field,
                        'old_value' => $oldVal,
                        'new_value' => $newVal,
                        'effective_date' => now(),
                        'notes' => "Perubahan $label via Admin Portal",
                    ]);
                }
            }
        }

        // 5. Update/Create User Files
        if ($request->hasFile('user_files')) {
            foreach ($request->file('user_files') as $type => $file) {
                if ($file) {
                    $path = $file->store('user_files', 'public');
                    \App\Models\UserFile::updateOrCreate(
                        ['user_id' => $user->id, 'file_type' => $type],
                        ['name' => $file->getClientOriginalName(), 'file_path' => $path]
                    );
                }
            }
        }

        return redirect()->route('admin.employees.show', $user)->with('success', 'Portal data karyawan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $validated = request()->validate([
            'reason' => 'required|in:resign,pensiun,habis_kontrak',
            'note' => 'nullable|string|max:2000',
        ]);

        $user = \App\Models\User::where('is_admin', false)->findOrFail($id);

        $user->deletion_reason = $validated['reason'];
        $user->deletion_note = $validated['note'] ?? null;
        $user->deleted_by = optional(auth()->user())->id;
        $user->save();

        $user->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil dinonaktifkan (soft delete)');
    }
}
