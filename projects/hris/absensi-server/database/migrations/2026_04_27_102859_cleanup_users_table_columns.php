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
            $table->dropColumn([
                'birth_place', 'marital_status', 'religion', 'nik', 'passport_no', 'blood_type',
                'education_level', 'education_institution', 'graduation_year', 'gpa',
                'ptkp_status', 'ptkp_year',
                'ktp_address', 'ktp_village', 'ktp_city', 'ktp_postal_code',
                'domicile_address', 'domicile_village', 'domicile_city', 'domicile_postal_code',
                'emergency_contact_info'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
