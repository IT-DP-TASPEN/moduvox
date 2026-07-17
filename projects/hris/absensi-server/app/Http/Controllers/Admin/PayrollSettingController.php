<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PayrollSetting;

class PayrollSettingController extends Controller
{
    public function index()
    {
        $settings = PayrollSetting::first() ?: PayrollSetting::create([
            'overtime_rate_permanent' => 30000,
            'overtime_rate_contract' => 25000,
            'overtime_meal_allowance' => 15000,
            'max_overtime_hours_contract' => 3,
            'payroll_day' => 25,
        ]);
        
        return view('admin.payroll_settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'overtime_rate_permanent' => 'required|numeric',
            'overtime_rate_contract' => 'required|numeric',
            'overtime_meal_allowance' => 'required|numeric',
            'max_overtime_hours_contract' => 'required|integer',
            'payroll_day' => 'required|integer|min:1|max:31',
        ]);

        $settings = PayrollSetting::first();
        $settings->update($validated);

        return redirect()->back()->with('success', 'Pengaturan payroll berhasil diperbarui!');
    }

    public function bulkIncrement(Request $request)
    {
        $request->validate([
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        $multiplier = 1 + ($request->percentage / 100);

        // Update all Gapok Master entries
        \App\Models\GapokMaster::all()->each(function($item) use ($multiplier) {
            $item->update(['amount' => $item->amount * $multiplier]);
        });

        // Update all Honorarium Master entries
        \App\Models\HonorariumMaster::all()->each(function($item) use ($multiplier) {
            $item->update(['amount' => $item->amount * $multiplier]);
        });

        return redirect()->back()->with('success', "Seluruh Master Gaji (Tetap & Kontrak) berhasil dinaikkan sebesar {$request->percentage}%!");
    }
}
