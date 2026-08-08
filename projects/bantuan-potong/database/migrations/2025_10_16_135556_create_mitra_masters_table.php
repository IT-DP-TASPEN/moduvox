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
        Schema::create('mitra_masters', function (Blueprint $table) {
            $table->id();
            $table->text('nama_mitra');
            $table->enum('jenis_fee_banpot', ['1', '2', '3']); //1.Dapem 2.Tagihan 3.Dapem-Saldo Mengendap
            $table->enum('jenis_pinbuk', ['1', '2']); //1.Seluruh Dapem 2.Nominal Tagihan
            $table->decimal('fee_banpot', 20, 2);
            $table->decimal('saldo_mengendap', 20, 2);
            $table->decimal('biaya_checking', 20, 2);
            $table->decimal('biaya_check_estimasi', 20, 2);
            $table->decimal('biaya_flagging_pensiun', 20, 2);
            $table->decimal('biaya_flagging_prapen', 20, 2);
            $table->decimal('biaya_flagging_tht', 20, 2);
            $table->decimal('biaya_flagging_prapen_tht', 20, 2);
            $table->decimal('biaya_flagging_mutasi_tif', 20, 2);
            $table->decimal('biaya_flagging_mutasi_tos', 20, 2);
            $table->decimal('ppn', 20, 2);
            $table->decimal('pph', 20, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_masters');
    }
};
