<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->decimal('jkk_company_rate', 5, 4)->default(0.0024);
            $table->decimal('jkm_company_rate', 5, 4)->default(0.0030);
            $table->decimal('jht_company_rate', 5, 4)->default(0.0370);
            $table->decimal('jp_company_rate', 5, 4)->default(0.0200);
            $table->decimal('jkn_company_rate', 5, 4)->default(0.0400);
        });
    }

    public function down(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->dropColumn([
                'jkk_company_rate',
                'jkm_company_rate',
                'jht_company_rate',
                'jp_company_rate',
                'jkn_company_rate'
            ]);
        });
    }
};
