<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\PermitRequestController;
use App\Http\Controllers\Admin\OvertimeRequestController;
use App\Http\Controllers\Admin\OutsideDutyRequestController;

Route::get('/', function () {
    // === DEMO BYPASS: Auto Login Admin ===
    if (!\Illuminate\Support\Facades\Auth::check()) {
        $admin = \App\Models\User::where('is_admin', true)->first();
        if ($admin) {
            \Illuminate\Support\Facades\Auth::login($admin);
        }
    }
    return redirect()->route('admin.dashboard');
});

Route::get('/auth/sso', [\App\Http\Controllers\Web\SsoController::class, 'login'])->name('sso.login')->middleware('signed');

Route::get('/login', function () {
    // If already logged in, go to dashboard
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return view('auth.login');
})->name('login');

// Route for Demo Login Button
Route::post('/demo-login', function (\Illuminate\Http\Request $request) {
    $admin = \App\Models\User::where('is_admin', true)->first();
    if ($admin) {
        \Illuminate\Support\Facades\Auth::login($admin);
        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }
    return back()->withErrors(['login' => 'Data admin tidak ditemukan, pastikan seeder sudah dijalankan.']);
})->name('demo.login');

// Route Download APK (Public)
Route::get('/download', function () {
    $settings = \App\Models\AppSetting::all()->pluck('value', 'key');
    return view('download.index', compact('settings'));
})->name('download.apk');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'login' => 'required|string',
        'password' => 'required|string',
    ]);

    $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    $credentials = [$loginField => $request->login, 'password' => $request->password];

    if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        $request->session()->regenerate();

        // Only allow admin users
        if (!auth()->user()->is_admin) {
            \Illuminate\Support\Facades\Auth::logout();
            return back()->withErrors(['login' => 'Akun Anda tidak memiliki akses ke panel admin.'])->withInput();
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    return back()->withErrors(['login' => 'Email/Username atau Password salah.'])->withInput();
})->name('login.submit');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::resource('global-allowances', \App\Http\Controllers\Admin\GlobalAllowanceController::class);
    Route::resource('offices', OfficeController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('divisions', \App\Http\Controllers\Admin\DivisionController::class);
    Route::resource('division-approvers', \App\Http\Controllers\Admin\DivisionApproverController::class);

    // Modul terpisah: cuti/izin/lembur/tugas luar
    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave_requests.index');
    Route::patch('/leave-requests/{leaveRequest}/status', [LeaveRequestController::class, 'updateStatus'])->name('leave_requests.status');

    Route::get('/permit-requests', [PermitRequestController::class, 'index'])->name('permit_requests.index');
    Route::patch('/permit-requests/{permitRequest}/status', [PermitRequestController::class, 'updateStatus'])->name('permit_requests.status');

    Route::get('/overtime-requests', [OvertimeRequestController::class, 'index'])->name('overtime_requests.index');
    Route::patch('/overtime-requests/{overtimeRequest}/status', [OvertimeRequestController::class, 'updateStatus'])->name('overtime_requests.status');

    Route::get('/outside-duty-requests', [OutsideDutyRequestController::class, 'index'])->name('outside_duty_requests.index');
    Route::patch('/outside-duty-requests/{outsideDutyRequest}/status', [OutsideDutyRequestController::class, 'updateStatus'])->name('outside_duty_requests.status');

    Route::get('/kpi', [\App\Http\Controllers\Admin\KpiController::class, 'index'])->name('kpi.index');
    Route::post('/kpi', [\App\Http\Controllers\Admin\KpiController::class, 'store'])->name('kpi.store');
    Route::resource('kpi-indicators', \App\Http\Controllers\Admin\KpiIndicatorController::class);

    Route::resource('salaries', \App\Http\Controllers\Admin\SalaryController::class);
    Route::post('/salaries/publish', [\App\Http\Controllers\Admin\SalaryController::class, 'publish'])->name('salaries.publish');
    Route::post('/salaries/disburse', [\App\Http\Controllers\Admin\SalaryController::class, 'disburse'])->name('salaries.disburse');
    Route::post('/salaries/generate-all', [\App\Http\Controllers\Admin\SalaryController::class, 'generateAll'])->name('salaries.generate-all');
    Route::post('/salaries/calculate', [\App\Http\Controllers\Admin\SalaryController::class, 'calculate'])->name('salaries.calculate');
    Route::get('/salaries/{id}/download-slip', [\App\Http\Controllers\Admin\SalaryController::class, 'downloadSlip'])->name('salaries.download-slip');

    Route::get('/app-settings', [\App\Http\Controllers\Admin\AppSettingController::class, 'index'])->name('app-settings.index');
    Route::put('/app-settings', [\App\Http\Controllers\Admin\AppSettingController::class, 'update'])->name('app-settings.update');

    Route::get('/payroll-settings', [\App\Http\Controllers\Admin\PayrollSettingController::class, 'index'])->name('payroll-settings.index');
    Route::put('/payroll-settings', [\App\Http\Controllers\Admin\PayrollSettingController::class, 'update'])->name('payroll-settings.update');
    Route::post('/payroll-settings/bulk-increment', [\App\Http\Controllers\Admin\PayrollSettingController::class, 'bulkIncrement'])->name('payroll-settings.bulk-increment');

    // Master Data (Gapok & Honorarium)
    Route::get('/master-data', [\App\Http\Controllers\Admin\MasterDataController::class, 'index'])->name('master-data.index');
    Route::post('/master-data/gapok', [\App\Http\Controllers\Admin\MasterDataController::class, 'storeGapok'])->name('master-data.gapok.store');
    Route::put('/master-data/gapok/{id}', [\App\Http\Controllers\Admin\MasterDataController::class, 'updateGapok'])->name('master-data.gapok.update');
    Route::delete('/master-data/gapok/{id}', [\App\Http\Controllers\Admin\MasterDataController::class, 'destroyGapok'])->name('master-data.gapok.destroy');
    Route::post('/master-data/honorarium', [\App\Http\Controllers\Admin\MasterDataController::class, 'storeHonorarium'])->name('master-data.honorarium.store');
    Route::put('/master-data/honorarium/{id}', [\App\Http\Controllers\Admin\MasterDataController::class, 'updateHonorarium'])->name('master-data.honorarium.update');
    Route::delete('/master-data/honorarium/{id}', [\App\Http\Controllers\Admin\MasterDataController::class, 'destroyHonorarium'])->name('master-data.honorarium.destroy');

    Route::resource('positions', \App\Http\Controllers\Admin\PositionController::class);
    Route::get('/api/positions/{id}/detail', [\App\Http\Controllers\Admin\PositionController::class, 'getDetail'])->name('positions.api-detail');
    Route::post('/api/positions/{id}/quick-allowance', [\App\Http\Controllers\Admin\PositionController::class, 'quickUpdateAllowance'])->name('positions.api-quick-allowance');
    Route::post('/api/positions/{id}/save-config', [\App\Http\Controllers\Admin\PositionController::class, 'saveFullConfig'])->name('positions.api-save-config');
    Route::post('/positions/division', [\App\Http\Controllers\Admin\PositionController::class, 'storeDivision'])->name('positions.store-division');
    Route::put('/positions/division/{id}', [\App\Http\Controllers\Admin\PositionController::class, 'updateDivision'])->name('positions.update-division');
    Route::delete('/positions/division/{id}', [\App\Http\Controllers\Admin\PositionController::class, 'destroyDivision'])->name('positions.destroy-division');
    Route::post('/positions/office/{id}/approvers', [\App\Http\Controllers\Admin\PositionController::class, 'updateOfficeApprover'])->name('positions.update-office-approver');
    Route::delete('/positions/office/approvers/{id}', [\App\Http\Controllers\Admin\PositionController::class, 'destroyOfficeApprover'])->name('positions.destroy-office-approver');
});
