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
            $table->string('npwp')->nullable()->after('bpjs_kesehatan_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            $table->dropColumn('npwp');
        });
    }
};
