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
            // Personal
            $table->string('birth_place')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('religion')->nullable();
            $table->string('nik')->nullable();
            $table->string('passport_no')->nullable();
            $table->string('blood_type', 5)->nullable();

            // Pendidikan
            $table->string('education_level')->nullable();
            $table->string('education_institution')->nullable();
            $table->string('graduation_year', 4)->nullable();
            $table->string('gpa', 10)->nullable();

            // PTKP
            $table->string('ptkp_status')->nullable();
            $table->string('ptkp_year', 4)->nullable();

            // Alamat KTP
            $table->text('ktp_address')->nullable();
            $table->string('ktp_village')->nullable();
            $table->string('ktp_city')->nullable();
            $table->string('ktp_postal_code', 10)->nullable();

            // Alamat Domisili
            $table->text('domicile_address')->nullable();
            $table->string('domicile_village')->nullable();
            $table->string('domicile_city')->nullable();
            $table->string('domicile_postal_code', 10)->nullable();

            // Keluarga & Kontak Darurat
            $table->text('emergency_contact_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
