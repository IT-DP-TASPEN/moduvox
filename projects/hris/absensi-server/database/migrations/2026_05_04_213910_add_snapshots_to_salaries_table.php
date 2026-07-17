<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->string('position_name_snapshot')->nullable();
            $table->string('division_name_snapshot')->nullable();
            $table->decimal('base_allowance_snapshot', 15, 2)->nullable();
            $table->decimal('kpi_score_snapshot', 5, 2)->nullable();
            $table->string('grade_snapshot')->nullable();
            $table->string('skg_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'position_name_snapshot',
                'division_name_snapshot',
                'base_allowance_snapshot',
                'kpi_score_snapshot',
                'grade_snapshot',
                'skg_snapshot'
            ]);
        });
    }
};
