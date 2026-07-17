<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->decimal('taspen_save_rate', 5, 4)->default(0.0400);
            $table->decimal('jht_employee_rate', 5, 4)->default(0.0200);
            $table->decimal('jp_employee_rate', 5, 4)->default(0.0100);
            $table->decimal('jkn_employee_rate', 5, 4)->default(0.0100);
            $table->decimal('pension_premium_rate', 5, 4)->default(0.0890);
            $table->decimal('dplk_bni_rate', 15, 2)->default(0); // Fixed or percentage? User mentioned "fasilitas DPLK BNI"
        });
    }

    public function down(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->dropColumn([
                'taspen_save_rate',
                'jht_employee_rate',
                'jp_employee_rate',
                'jkn_employee_rate',
                'pension_premium_rate',
                'dplk_bni_rate'
            ]);
        });
    }
};
