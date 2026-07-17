<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Tabel Gaji Dasar (GAPOK 2026)
        Schema::create('gapok_masters', function (Blueprint $table) {
            $table->id();
            $table->integer('skg'); // 1 - 30
            $table->string('grade'); // I - X
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            
            $table->unique(['skg', 'grade']);
        });

        // 2. Master Tabel Honorarium (Kontrak)
        Schema::create('honorarium_masters', function (Blueprint $table) {
            $table->id();
            $table->string('position_name'); // e.g., Branch Manager
            $table->string('level'); // MUDA, MADYA, UTAMA
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            
            $table->unique(['position_name', 'level']);
        });

        // 3. Pengaturan Payroll (Parameter Dinamis)
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('overtime_rate_permanent', 15, 2)->default(0);
            $table->decimal('overtime_rate_contract', 15, 2)->default(25000);
            $table->decimal('overtime_meal_allowance', 15, 2)->default(0);
            $table->integer('max_overtime_hours_contract')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gapok_masters');
        Schema::dropIfExists('honorarium_masters');
        Schema::dropIfExists('payroll_settings');
    }
};
