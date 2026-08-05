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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique();
            $table->string('employee_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('title')->nullable();
            $table->string('unit_name')->nullable();
            $table->string('division_name')->nullable();
            $table->string('office_type')->nullable();
            $table->string('branch_code')->nullable();
            $table->boolean('is_admin')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'employee_id', 'phone', 'title', 
                'unit_name', 'division_name', 'office_type', 
                'branch_code', 'is_admin'
            ]);
        });
    }
};
