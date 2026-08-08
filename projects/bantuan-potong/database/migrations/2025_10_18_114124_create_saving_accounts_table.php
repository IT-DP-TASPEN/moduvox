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
        Schema::create('saving_accounts', function (Blueprint $table) {
            $table->id();
            $table->text('wilayah')->nullable();
            $table->string('notas');
            $table->text('customer_name');
            $table->string('national_id_number');
            $table->string('identity_type');
            $table->string('alternate_number')->nullable();
            $table->string('mobile_phone');
            $table->text('place_of_birth');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('religion');
            $table->text('mother_maiden_name');
            $table->text('address');
            $table->string('dati2_code');
            $table->text('dati2_name');
            $table->text('urban_village');
            $table->text('sub_district');
            $table->string('postal_code');
            $table->text('province');
            $table->string('tax_id')->nullable();
            $table->text('customer_alias_name')->nullable();
            $table->string('sid_status')->nullable();
            $table->string('debtor_in_city_administrative')->nullable();
            $table->string('debtor_type_other')->nullable();
            $table->string('debtor_type')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('last_edu')->nullable();
            $table->text('nama_pasangan')->nullable();
            $table->string('nik_pasangan')->nullable();
            $table->string('kontak_darurat')->nullable();
            $table->text('nama_ahli_waris')->nullable();
            $table->string('hub_ahli_waris')->nullable();
            $table->string('form_buka_tab')->nullable();
            $table->string('saving_booknumber')->nullable();
            $table->string('customer_id')->nullable();
            $table->enum('status', [
                'request',        // pengajuan mitra
                'approved_mitra', // disetujui atasan mitra
                'rejected_mitra', // ditolak atasan mitra
                'canceled',       // dibatalkan
                'on_process',     // diproses di backend
                'success',        // berhasil
                'failed',         // gagal
                'complete',       // selesai semua
            ])->default('request');
            $table->enum('status_cif', [
                'on_process',
                'success',
                'failed',
            ])->default('on_process');
            $table->enum('status_saving', [
                'on_process',
                'success',
                'failed',
            ])->default('on_process');
            $table->text('keterangan')->nullable();
            $table->text('keterangan_2')->nullable();
            $table->string('rek_tabungan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->text('created_mitra');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saving_accounts');
    }
};
