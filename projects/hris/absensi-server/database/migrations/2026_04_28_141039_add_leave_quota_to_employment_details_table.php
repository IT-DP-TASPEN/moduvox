<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            $table->integer('leave_quota')->default(12)->after('dplk_bni_account_number');
            $table->integer('remaining_leave')->default(12)->after('leave_quota');
        });
    }

    public function down(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            $table->dropColumn(['leave_quota', 'remaining_leave']);
        });
    }
};
