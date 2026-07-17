<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_allowances', function (Blueprint $table) {
            $table->enum('category', ['earning', 'deduction', 'company_paid'])->default('earning')->after('type');
        });

        // Migrate existing data
        DB::statement("UPDATE global_allowances SET category = 'deduction' WHERE is_deduction = 1");
        DB::statement("UPDATE global_allowances SET category = 'earning' WHERE is_deduction = 0");

        Schema::table('global_allowances', function (Blueprint $table) {
            $table->dropColumn('is_deduction');
        });
    }

    public function down(): void
    {
        Schema::table('global_allowances', function (Blueprint $table) {
            $table->boolean('is_deduction')->default(false)->after('type');
        });

        // Migrate back
        DB::statement("UPDATE global_allowances SET is_deduction = 1 WHERE category = 'deduction'");

        Schema::table('global_allowances', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
