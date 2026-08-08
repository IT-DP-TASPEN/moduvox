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
        Schema::create('permintaan_flagging_mutasi_tif_internals', function (Blueprint $table) {
            $table->id();
            $table->text('wilayah')->nullable();
            $table->text('nama_nasabah');
            $table->string('notas')->nullable();
            $table->string('nik')->nullable();
            $table->text('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_handphone')->nullable();
            $table->string('rek_tabungan')->nullable();
            $table->string('rek_kredit')->nullable();
            $table->date('tmt_kredit')->nullable();
            $table->date('tat_kredit')->nullable();
            $table->string('ktp')->nullable();
            $table->string('sp_deb_flagging')->nullable();
            $table->string('foto_tab')->nullable();
            $table->string('form_pindah_kantor')->nullable();
            $table->decimal('fee', 20, 2)->nullable();
            $table->decimal('fee_checking', 20, 2)->nullable();
            $table->enum('status', [
                'request',        // pengajuan mitra
                'approved', // disetujui atasan mitra
                'rejected', // ditolak atasan mitra
                'canceled',       // dibatalkan
                'on_process',     // diproses di backend
                'success',        // berhasil
                'failed',         // gagal
                'complete',       // selesai semua
            ])->default('request');
            $table->text('keterangan')->nullable();
            $table->string('bukti_hasil')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('marketing_id')->constrained('marketing_masters')->cascadeOnDelete();
            $table->text('reference')->nullable();
            $table->text('created_branch')->nullable();
            $table->text('branch_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_flagging_mutasi_tif_internals');
    }
};
