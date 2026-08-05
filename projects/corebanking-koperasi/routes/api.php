<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\AccountController;
use App\Http\Controllers\Mobile\PinController;

/*
|--------------------------------------------------------------------------
| Mobile Banking API Routes
|--------------------------------------------------------------------------
| Prefix  : /api/mobile
| Auth    : Bearer Token via App\Http\Middleware\MobileAuth
|
*/

Route::prefix('mobile')->group(function () {

    // ── Autentikasi (tidak perlu token) ──────────────────────────────
    Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
        Route::post('login',             [AuthController::class, 'login']);
        Route::post('verify-activation', [AuthController::class, 'verifyActivation']);
        Route::post('activate',          [AuthController::class, 'activate']);
    });

    // ── Endpoint yang memerlukan token mobile ────────────────────────
    Route::middleware(['throttle:120,1', \App\Http\Middleware\MobileAuth::class])->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout',          [AuthController::class, 'logout']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });

        // Dashboard & Profil
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('profile',   [DashboardController::class, 'profile']);

        // PIN Management
        Route::prefix('pin')->group(function () {
            Route::post('verify', [PinController::class, 'verify']);
            Route::post('change', [PinController::class, 'change']);
        });

        // Rekening
        Route::prefix('accounts')->group(function () {
            // Tabungan
            Route::get('savings', [AccountController::class, 'savings']);
            Route::get('savings/{account_no}/transactions', [AccountController::class, 'savingTransactions']);

            // Kredit
            Route::get('loans',   [AccountController::class, 'loans']);

            // Deposito
            Route::get('deposits', [AccountController::class, 'deposits']);
        });

        // Kompatibilitas endpoint mobile lama
        Route::get('mutasi', [AccountController::class, 'mutasi']);
    });
});
