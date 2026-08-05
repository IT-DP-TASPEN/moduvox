<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cifs', function (Blueprint $table) {
            $table->id();
            $table->string('cif_no')->unique();
            $table->string('nik', 20)->unique();
            $table->string('npwp', 30)->nullable();
            $table->string('name');

            // Demographics
            $table->string('birth_place');
            $table->date('birth_date');
            $table->enum('gender', ['MALE', 'FEMALE']);
            $table->enum('blood_type', ['A', 'B', 'AB', 'O'])->nullable();
            $table->string('religion')->nullable();
            $table->enum('marital_status', ['SINGLE', 'MARRIED', 'WIDOWED', 'DIVORCED'])->default('SINGLE');
            $table->string('mother_maiden_name');

            // Geography & Contact
            $table->text('address');
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->unsignedBigInteger('province_id');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('subdistrict_id');
            $table->string('postal_code', 20)->nullable();
            $table->text('domicile_address')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();

            // Occupation & Financials
            $table->string('occupation')->nullable();
            $table->string('occupation_nip')->nullable()->comment('NIP / Nomor Pegawai');
            $table->string('company_name')->nullable();
            $table->string('income_range')->nullable();

            // Spouse / Emergency Contact
            $table->string('spouse_name')->nullable();
            $table->string('spouse_nik')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_phone')->nullable();

            // Administrative
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('marketing_id')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'BLOCKED'])->default('ACTIVE');

            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Foreign Keys
            $table->foreign('province_id')->references('id')->on('provinces');
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('district_id')->references('id')->on('districts');
            $table->foreign('subdistrict_id')->references('id')->on('subdistricts');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('marketing_id')->references('id')->on('marketing_masters');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');

            $table->index(['branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cifs');
    }
};