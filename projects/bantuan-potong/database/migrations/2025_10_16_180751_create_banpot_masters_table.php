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
        Schema::create('banpot_masters', function (Blueprint $table) {
            $table->id();
            $table->string('rek_tabungan')->nullable();
            $table->string('nama_nasabah')->nullable();
            $table->string('notas')->nullable();
            $table->string('rek_kredit')->nullable();
            $table->string('tenor')->nullable();
            $table->string('angsuran_ke')->nullable();
            $table->date('tmt_kredit')->nullable();
            $table->date('tat_kredit')->nullable();
            $table->decimal('gaji_pensiun', 20, 2)->nullable();
            $table->decimal('nominal_potongan', 20, 2)->nullable();
            $table->string('bank_transfer')->nullable();
            $table->string('rek_transfer')->nullable();
            $table->decimal('saldo_mengendap', 20, 2)->nullable();
            $table->decimal('jumlah_tertagih', 20, 2)->nullable();
            $table->decimal('gaji_mengendap', 20, 2)->nullable();
            $table->decimal('sisa_gaji', 20, 2)->nullable();
            $table->decimal('fee_banpot', 20, 2)->nullable();
            $table->boolean('rek_tabungan_valid')->nullable();
            $table->boolean('notas_valid')->nullable();
            $table->boolean('dapem_valid')->nullable();
            $table->boolean('oten_valid')->nullable();
            $table->string('oten_type')->nullable();
            $table->boolean('final_validasi_status')->nullable();
            $table->string('bulan_dapem');
            $table->text('keterangan')->nullable();
            $table->text('keterangan_2')->nullable();
            $table->enum('jenis_pinbuk', ['1', '2']); //1.Seluruh Dapem 2.Nominal Tagihan
            $table->date('next_due_date')->nullable();     // nextDueDate
            $table->enum('status_banpot', [
                'request',        // pengajuan mitra
                'approved_mitra', // disetujui atasan mitra
                'rejected_mitra', // ditolak atasan mitra
                'canceled',       // dibatalkan
                'on_process',     // diproses di backend
                'success',        // berhasil
                'failed',         // gagal
                'complete',       // selesai semua
            ])->default('request');
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('created_mitra');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banpot_masters');
    }
};
