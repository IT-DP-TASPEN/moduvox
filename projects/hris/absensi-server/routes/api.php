<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\PermitRequestController;
use App\Http\Controllers\Api\OvertimeRequestController;
use App\Http\Controllers\Api\OutsideDutyRequestController;
use App\Http\Controllers\Api\SsoController;
use App\Http\Controllers\Api\SalaryController;
use App\Http\Controllers\Api\KpiController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-pin', [AuthController::class, 'loginPin']);

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/verify-pin', [AuthController::class, 'verifyPin']);
    Route::post('/set-pin', [AuthController::class, 'setPin']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['office', 'mutations', 'warnings', 'files', 'profile', 'employment', 'approverSettings', 'directorSettings']);
    });
    
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/change-pin', [AuthController::class, 'changePin']);
    
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);
    Route::get('/attendance/recap', [AttendanceController::class, 'recap']);

    Route::get('/users', [\App\Http\Controllers\Api\UserController::class, 'index']);

    // Modul terpisah: cuti/izin/lembur/tugas luar
    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::get('/leave-requests/pending', [LeaveRequestController::class, 'pendingApprovals']);
    Route::post('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);

    Route::get('/permit-requests', [PermitRequestController::class, 'index']);
    Route::post('/permit-requests', [PermitRequestController::class, 'store']);
    Route::get('/permit-requests/pending', [PermitRequestController::class, 'pendingApprovals']);
    Route::post('/permit-requests/{id}/approve', [PermitRequestController::class, 'approve']);
    Route::post('/permit-requests/{id}/reject', [PermitRequestController::class, 'reject']);

    Route::get('/overtime-requests', [OvertimeRequestController::class, 'index']);
    Route::post('/overtime-requests', [OvertimeRequestController::class, 'store']);
    Route::get('/overtime-requests/pending', [OvertimeRequestController::class, 'pendingApprovals']);
    Route::post('/overtime-requests/{id}/approve', [OvertimeRequestController::class, 'approve']);
    Route::post('/overtime-requests/{id}/reject', [OvertimeRequestController::class, 'reject']);

    Route::get('/outside-duty-requests', [OutsideDutyRequestController::class, 'index']);
    Route::post('/outside-duty-requests', [OutsideDutyRequestController::class, 'store']);
    Route::get('/outside-duty-requests/pending', [OutsideDutyRequestController::class, 'pendingApprovals']);
    Route::post('/outside-duty-requests/{id}/approve', [OutsideDutyRequestController::class, 'approve']);
    Route::post('/outside-duty-requests/{id}/reject', [OutsideDutyRequestController::class, 'reject']);

    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/user/update-photo', [\App\Http\Controllers\Api\UserController::class, 'updatePhoto']);
    Route::get('/auth/sso-link', [SsoController::class, 'generateToken']);

    Route::get('/salaries', [SalaryController::class, 'index']);
    Route::get('/salaries/{id}', [SalaryController::class, 'show']);
    Route::get('/salaries/{id}/download-slip', [SalaryController::class, 'downloadSlip']);
    Route::get('/kpis', [KpiController::class, 'index']);
    Route::post('/kpis', [KpiController::class, 'store']);
    Route::get('/banners', [\App\Http\Controllers\Api\BannerController::class, 'index']);
});
