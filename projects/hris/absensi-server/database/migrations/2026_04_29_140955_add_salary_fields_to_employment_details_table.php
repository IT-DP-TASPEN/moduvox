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
        Schema::table('employment_details', function (Blueprint $table) {
            $table->decimal('basic_salary', 15, 2)->default(0)->after('user_id');
            $table->decimal('position_allowance', 15, 2)->default(0)->after('basic_salary');
            $table->decimal('max_performance_allowance', 15, 2)->default(0)->after('position_allowance');
        });
    }

    public function down(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            $table->dropColumn(['basic_salary', 'position_allowance', 'max_performance_allowance']);
        });
    }
};
