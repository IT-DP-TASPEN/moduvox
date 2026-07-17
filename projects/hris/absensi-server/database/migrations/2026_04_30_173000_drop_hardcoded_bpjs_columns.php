<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'jht_employee',
                'jp_employee',
                'jkn_employee',
                'taspen_save_deduction',
                'jkk_company',
                'jkm_company',
                'jht_company',
                'jp_company',
                'jkn_company',
                'pension_premium',
                'taspen_save_allowance'
            ]);
        });

        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->dropColumn([
                'taspen_save_rate',
                'jht_employee_rate',
                'jp_employee_rate',
                'jkn_employee_rate',
                'pension_premium_rate',
                'jkk_company_rate',
                'jkm_company_rate',
                'jht_company_rate',
                'jp_company_rate',
                'jkn_company_rate',
                'dplk_bni_rate'
            ]);
        });
    }

    public function down(): void
    {
        // Re-adding the columns if rolling back
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('jht_employee', 15, 2)->default(0);
            $table->decimal('jp_employee', 15, 2)->default(0);
            $table->decimal('jkn_employee', 15, 2)->default(0);
            $table->decimal('taspen_save_deduction', 15, 2)->default(0);
            $table->decimal('jkk_company', 15, 2)->default(0);
            $table->decimal('jkm_company', 15, 2)->default(0);
            $table->decimal('jht_company', 15, 2)->default(0);
            $table->decimal('jp_company', 15, 2)->default(0);
            $table->decimal('jkn_company', 15, 2)->default(0);
            $table->decimal('pension_premium', 15, 2)->default(0);
            $table->decimal('taspen_save_allowance', 15, 2)->default(0);
        });

        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->decimal('taspen_save_rate', 5, 4)->default(0);
            $table->decimal('jht_employee_rate', 5, 4)->default(0);
            $table->decimal('jp_employee_rate', 5, 4)->default(0);
            $table->decimal('jkn_employee_rate', 5, 4)->default(0);
            $table->decimal('pension_premium_rate', 5, 4)->default(0);
            $table->decimal('jkk_company_rate', 5, 4)->default(0);
            $table->decimal('jkm_company_rate', 5, 4)->default(0);
            $table->decimal('jht_company_rate', 5, 4)->default(0);
            $table->decimal('jp_company_rate', 5, 4)->default(0);
            $table->decimal('jkn_company_rate', 5, 4)->default(0);
            $table->decimal('dplk_bni_rate', 5, 4)->default(0);
        });
    }
};
