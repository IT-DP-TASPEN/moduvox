<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\MstKantorController;
use App\Http\Controllers\Master\MstGolonganController;
use App\Http\Controllers\Master\MstJenisController;
use App\Http\Controllers\Master\MstLokasiController;
use App\Http\Controllers\Master\MstRuanganController;
use App\Http\Controllers\Master\MstSumberDanaController;
use App\Http\Controllers\ResetDataController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Public route for Barcode Scan
Route::get('scan/{id}', [\App\Http\Controllers\InventarisController::class, 'publicScan'])->name('inventaris.scan');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        // Kantor Cabang
        Route::get('kantor/data', [MstKantorController::class, 'data'])->name('kantor.data');
        Route::resource('kantor', MstKantorController::class)->except(['create', 'edit']);

        // Golongan Aset
        Route::get('golongan/data', [MstGolonganController::class, 'data'])->name('golongan.data');
        Route::resource('golongan', MstGolonganController::class)->except(['create', 'edit']);

        // Jenis Aset
        Route::get('jenis/data', [MstJenisController::class, 'data'])->name('jenis.data');
        Route::resource('jenis', MstJenisController::class)->except(['create', 'edit']);

        // Lokasi
        Route::get('lokasi/data', [MstLokasiController::class, 'data'])->name('lokasi.data');
        Route::resource('lokasi', MstLokasiController::class)->except(['create', 'edit']);

        // Ruangan
        Route::get('ruangan/data', [MstRuanganController::class, 'data'])->name('ruangan.data');
        Route::resource('ruangan', MstRuanganController::class)->except(['create', 'edit']);

        // Sumber Dana
        Route::get('sumber-dana/data', [MstSumberDanaController::class, 'data'])->name('sumber-dana.data');
        Route::resource('sumber-dana', MstSumberDanaController::class)->except(['create', 'edit']);
    });

    // Inventaris (Core Aset)
    Route::get('inventaris/data', [\App\Http\Controllers\InventarisController::class, 'data'])->name('inventaris.data');
    Route::post('inventaris/import', [\App\Http\Controllers\InventarisController::class, 'import'])->name('inventaris.import');
    Route::get('inventaris/print-massal', [\App\Http\Controllers\InventarisController::class, 'printLabelMassal'])->name('inventaris.print-massal');
    Route::get('inventaris/{id}/print', [\App\Http\Controllers\InventarisController::class, 'printLabel'])->name('inventaris.print');
    Route::resource('inventaris', \App\Http\Controllers\InventarisController::class);

    // Mutasi
    Route::get('inventaris/{id}/mutasi', [\App\Http\Controllers\InvMutasiController::class, 'create'])->name('mutasi.create');
    Route::post('inventaris/{id}/mutasi', [\App\Http\Controllers\InvMutasiController::class, 'store'])->name('mutasi.store');

    // Ajax helpers
    Route::get('api/master/ruangan-by-kantor/{kantor_id}', [\App\Http\Controllers\InventarisController::class, 'getRuanganByKantor']);

    // Penyusutan Batch
    Route::resource('penyusutan', \App\Http\Controllers\PenyusutanController::class)->only(['index', 'store', 'show']);
    Route::post('penyusutan/{id}/approve', [\App\Http\Controllers\PenyusutanController::class, 'approve'])->name('penyusutan.approve');

    // API Journals
    Route::get('api-journals', [\App\Http\Controllers\ApiJournalController::class, 'index'])->name('api-journals.index');
    Route::get('api-journals/export', [\App\Http\Controllers\ApiJournalController::class, 'export'])->name('api-journals.export');
    Route::get('api-journals/{id}/detail', [\App\Http\Controllers\ApiJournalController::class, 'detail'])->name('api-journals.detail');
    Route::post('api-journals/{id}/retry', [\App\Http\Controllers\ApiJournalController::class, 'retry'])->name('api-journals.retry');

    // Reports
    Route::get('reports/nominatif', [\App\Http\Controllers\ReportController::class, 'nominatifIndex'])->name('reports.nominatif.index');
    Route::get('reports/nominatif/generate', [\App\Http\Controllers\ReportController::class, 'nominatifGenerate'])->name('reports.nominatif.generate');
    Route::get('reports/penyusutan', [\App\Http\Controllers\ReportController::class, 'penyusutanIndex'])->name('reports.penyusutan.index');
    Route::get('reports/penyusutan/generate', [\App\Http\Controllers\ReportController::class, 'penyusutanGenerate'])->name('reports.penyusutan.generate');

    // Asset Specific Routes
    Route::post('/motor/import', [App\Http\Controllers\InventarisMotorController::class, 'import'])->name('motor.import');
    Route::get('/motor/data', [App\Http\Controllers\InventarisMotorController::class, 'data'])->name('motor.data');
    Route::get('/motor', [App\Http\Controllers\InventarisMotorController::class, 'index'])->name('motor.index');
    
    Route::post('/tanah/import', [App\Http\Controllers\InventarisTanahController::class, 'import'])->name('tanah.import');
    Route::get('/tanah/data', [App\Http\Controllers\InventarisTanahController::class, 'data'])->name('tanah.data');
    Route::get('/tanah', [App\Http\Controllers\InventarisTanahController::class, 'index'])->name('tanah.index');
    
    Route::get('penyusutan-list/data', [\App\Http\Controllers\InventarisPenyusutanController::class, 'data'])->name('penyusutan_list.data');
    Route::post('penyusutan-list/import', [\App\Http\Controllers\InventarisPenyusutanController::class, 'import'])->name('penyusutan_list.import');
    Route::get('/penyusutan-list', [App\Http\Controllers\InventarisPenyusutanController::class, 'index'])->name('penyusutan_list.index');
    
    Route::post('/transaksi/import', [App\Http\Controllers\InventarisTransaksiController::class, 'import'])->name('transaksi.import');
    Route::get('/transaksi/data', [App\Http\Controllers\InventarisTransaksiController::class, 'data'])->name('transaksi.data');
    Route::get('/transaksi/{id}', [App\Http\Controllers\InventarisTransaksiController::class, 'show'])->name('transaksi.show');
    Route::get('/transaksi', [App\Http\Controllers\InventarisTransaksiController::class, 'index'])->name('transaksi.index');

    // System
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('users/data', [\App\Http\Controllers\UserController::class, 'data'])->name('users.data');
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'edit']);

        Route::get('audit', [\App\Http\Controllers\AuditTrailController::class, 'index'])->name('audit.index');
        Route::get('audit/data', [\App\Http\Controllers\AuditTrailController::class, 'data'])->name('audit.data');

        // Reset Data
        Route::get('reset', [ResetDataController::class, 'index'])->name('reset.index');
        Route::post('reset/inventaris', [ResetDataController::class, 'resetInventaris'])->name('reset.inventaris');
    });
});
