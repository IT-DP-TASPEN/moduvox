<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_allowances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('amount', 15, 4); // Can store fixed amount or percentage (e.g. 0.05)
            $table->enum('type', ['fixed', 'percentage_gapok'])->default('fixed');
            $table->boolean('is_deduction')->default(false); // If true, it's a deduction (potongan)
            $table->string('target_status')->default('All'); // 'Tetap', 'Kontrak', 'OJT', 'PE', 'All'
            $table->timestamps();
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->json('dynamic_components')->nullable()->after('performance_allowance');
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn('dynamic_components');
        });
        Schema::dropIfExists('global_allowances');
    }
};
