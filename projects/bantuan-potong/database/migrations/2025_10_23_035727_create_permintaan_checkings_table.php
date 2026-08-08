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
        Schema::create('permintaan_checkings', function (Blueprint $table) {
            $table->id();
            $table->text('wilayah')->nullable();
            $table->text('nama_nasabah');
            $table->string('notas')->nullable();
            $table->string('lampiran')->nullable();
            $table->decimal('fee', 20, 2)->nullable();
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
            $table->text('keterangan')->nullable();
            $table->string('bukti_hasil')->nullable();
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
        Schema::dropIfExists('permintaan_checkings');
    }
};
