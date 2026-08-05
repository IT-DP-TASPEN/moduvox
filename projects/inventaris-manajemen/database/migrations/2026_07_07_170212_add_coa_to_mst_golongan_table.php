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
        Schema::table('mst_golongan', function (Blueprint $table) {
            $table->string('akun_debet', 20)->nullable()->after('umur_standar');
            $table->string('akun_kredit', 20)->nullable()->after('akun_debet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_golongan', function (Blueprint $table) {
            $table->dropColumn(['akun_debet', 'akun_kredit']);
        });
    }
};
