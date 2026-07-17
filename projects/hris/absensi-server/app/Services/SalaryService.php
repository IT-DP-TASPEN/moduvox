<?php

namespace App\Services;

use App\Models\User;
use App\Models\KpiRecord;
use App\Models\Salary;

class SalaryService
{
    /**
     * Calculate all salary components based on master data and monthly variables.
     */
    public function calculate(User $user, int $month, int $year)
    {
        $employment = $user->employment;
        if (!$employment) {
            throw new \Exception("Data employment tidak ditemukan untuk user ini.");
        }

        $settings = \App\Models\PayrollSetting::first();
        $basicSalary = 0;

        // 1. Determine Basic Salary from Master Tables
        if ($user->employment_status === 'Tetap') {
            $master = \App\Models\GapokMaster::where('skg', $employment->skg)
                ->where('grade', $employment->grade)
                ->first();
            $basicSalary = $master ? $master->amount : ($employment->basic_salary ?: 0);
        } elseif (in_array($user->employment_status, ['Kontrak', 'OJT', 'PE'])) {
            $level = strtoupper($employment->grade); 
            $master = \App\Models\HonorariumMaster::where('position_name', $employment->position)
                ->where('level', $level)
                ->first();
            
            if ($master) {
                $basicSalary = $master->amount;
            } else {
                // Specific default for OJT if not found in master table
                if ($user->employment_status === 'OJT') {
                    $basicSalary = 3000000;
                } else {
                    // Fallback for Contract/PE
                    $masterFallback = \App\Models\HonorariumMaster::where('position_name', 'Kontrak Langsung Jabodetabek')->first();
                    $basicSalary = $masterFallback ? $masterFallback->amount : ($employment->basic_salary ?: 0);
                }
            }
        }

        // 2. Fetch Automated Overtime Hours from Overtime Requests
        $overtimeHours = \App\Models\OvertimeRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereMonth('start_at', $month)
            ->whereYear('start_at', $year)
            ->get()
            ->sum(function($request) {
                $start = \Carbon\Carbon::parse($request->start_at);
                $end = \Carbon\Carbon::parse($request->end_at);
                $diffInMinutes = $start->diffInMinutes($end);
                $netMinutes = $diffInMinutes - ($request->break_minutes ?? 0);
                return max(0, $netMinutes / 60);
            });

        // 3. Calculate Overtime Pay based on Settings
        $overtimePay = 0;
        $overtimeMealPay = 0;

        if ($overtimeHours > 0) {
            if ($user->employment_status === 'Tetap') {
                $overtimePay = $overtimeHours * $settings->overtime_rate_permanent;
                $overtimeMealPay = $settings->overtime_meal_allowance;
            } else {
                $maxHours = $settings->max_overtime_hours_contract;
                $effectiveHours = min($overtimeHours, $maxHours);
                $overtimePay = $effectiveHours * $settings->overtime_rate_contract;
                $overtimeMealPay = 0;
            }
        }

        $positionAllowance = 0;
        $maxPerformanceAllowance = 0;
        $performanceAllowance = 0;

        if ($user->employment_status === 'Tetap') {
            // 4. Position Allowance Logic
            $baseAllowance = 0;
            
            // Priority 1: Individual Override
            if ($employment->allowance_override > 0) {
                $baseAllowance = $employment->allowance_override;
            } 
            // Priority 2: Position Master
            elseif ($employment->position_id) {
                $pos = $employment->positionMaster;
                $allowanceRecord = $pos->currentAllowance();
                $baseAllowance = $allowanceRecord ? $allowanceRecord->amount : 0;
            }

            // Fetch KPI score for the month (Default to 100 if not found)
            $kpi = KpiRecord::where('user_id', $user->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();
            
            $kpiScore = $kpi ? $kpi->score : 100.00; // Default factor 1.0 (100%)
            
            // FINAL CALCULATION: Base * (Score / 100)
            $positionAllowance = $baseAllowance * ($kpiScore / 100);
            
            // Performance Allowance (Secondary Component if still used)
            $maxPerformanceAllowance = $employment->max_performance_allowance;
            if ($maxPerformanceAllowance <= 0) {
                $title = strtolower($user->title);
                if (str_contains($title, 'direktur') || str_contains($title, 'dirut')) {
                    $maxPerformanceAllowance = 15000000;
                } elseif (str_contains($title, 'kepala divisi') || str_contains($title, 'kadiv')) {
                    $maxPerformanceAllowance = 7500000;
                } else {
                    $maxPerformanceAllowance = 1500000;
                }
            }
            $performanceAllowance = ($kpiScore / 100) * $maxPerformanceAllowance;
        }

        // 5. Dynamic Global Components
        $globalComponents = \App\Models\GlobalAllowance::all();
        $dynamicAllowancesTotal = 0;
        $dynamicDeductionsTotal = 0;
        $dynamicNonThpTotal = 0;
        $dynamicComponentsBreakdown = [];

        foreach ($globalComponents as $component) {
            // Check target status
            if ($component->target_status !== 'All' && $component->target_status !== $user->employment_status) {
                continue;
            }

            // Calculate amount
            $amount = 0;
            if ($component->type === 'percentage_gapok') {
                $amount = $basicSalary * $component->amount;
            } else {
                $amount = $component->amount;
            }

            if ($amount > 0) {
                $dynamicComponentsBreakdown[] = [
                    'id' => $component->id,
                    'name' => $component->name,
                    'amount' => $amount,
                    'category' => $component->category
                ];

                if ($component->category === 'deduction') {
                    $dynamicDeductionsTotal += $amount;
                } elseif ($component->category === 'earning') {
                    $dynamicAllowancesTotal += $amount;
                } elseif ($component->category === 'company_paid') {
                    $dynamicNonThpTotal += $amount;
                }
            }
        }

        $taxAllowance = 0; 
        $incomeTax = 0; 

        $totalEarnings = $basicSalary + $overtimePay + $overtimeMealPay + $positionAllowance + $performanceAllowance + $dynamicAllowancesTotal + $taxAllowance;
        $totalDeductions = $incomeTax + $dynamicDeductionsTotal;

        return [
            'user_id' => $user->id,
            'month' => $month,
            'year' => $year,
            'basic_salary' => $basicSalary,
            'overtime_hours' => $overtimeHours,
            'overtime_pay' => $overtimePay,
            'overtime_meal_pay' => $overtimeMealPay,
            'tax_allowance' => $taxAllowance,
            'position_allowance' => $positionAllowance,
            'performance_allowance' => $performanceAllowance,
            'dynamic_components' => json_encode($dynamicComponentsBreakdown),
            'income_tax' => $incomeTax,
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => $totalEarnings - $totalDeductions,
            'total_non_thp' => $dynamicNonThpTotal,
            'total_gross' => $totalEarnings + $dynamicNonThpTotal,
            
            // Snapshot Data
            'position_name_snapshot' => $employment->positionMaster?->name ?: $employment->position,
            'division_name_snapshot' => $employment->positionMaster?->division?->name ?: $employment->department,
            'base_allowance_snapshot' => $baseAllowance ?? 0,
            'kpi_score_snapshot' => $kpiScore ?? 100,
            'grade_snapshot' => $employment->grade,
            'skg_snapshot' => $employment->skg,
        ];
    }

    /**
     * Convert a number to Indonesian words (terbilang).
     */
    public static function terbilang($angka): string
    {
        $angka = abs($angka);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($angka < 12) {
            return ' ' . $huruf[$angka];
        } elseif ($angka < 20) {
            return self::terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            return self::terbilang(intval($angka / 10)) . ' Puluh' . self::terbilang($angka % 10);
        } elseif ($angka < 200) {
            return ' Seratus' . self::terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return self::terbilang(intval($angka / 100)) . ' Ratus' . self::terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return ' Seribu' . self::terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return self::terbilang(intval($angka / 1000)) . ' Ribu' . self::terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return self::terbilang(intval($angka / 1000000)) . ' Juta' . self::terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            return self::terbilang(intval($angka / 1000000000)) . ' Miliar' . self::terbilang($angka % 1000000000);
        }
        return '';
    }
    public static function getPaymentDate($month, $year)
    {
        $settings = \App\Models\PayrollSetting::first();
        $targetDay = $settings ? $settings->payroll_day : 25;
        
        $date = \Carbon\Carbon::create($year, $month, $targetDay);
        
        // If it's Saturday (6), move to Friday (-1 day)
        if ($date->isSaturday()) {
            $date->subDay();
        } 
        // If it's Sunday (0), move to Friday (-2 days)
        elseif ($date->isSunday()) {
            $date->subDays(2);
        }
        
        return $date;
    }
}
