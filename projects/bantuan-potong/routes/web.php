<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/tab/{transaksiSimpananBerjangka}', function (App\Models\SavingAccount $transaksiSimpananBerjangka) {
    return view('form_tab', ['tab' => $transaksiSimpananBerjangka]);
})->name('form_tab');

Route::get('/tab-internal/{transaksiSimpananInternal}', function (App\Models\SavingAccountInternal $transaksiSimpananInternal) {
    return view('form_tab_internal', ['tab_internal' => $transaksiSimpananInternal]);
})->name('form_tab_internal');
