<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('branch_masters', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('branch_name');
        });

        Schema::table('marketing_masters', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('jenis_marketing');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_masters', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('branch_masters', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
