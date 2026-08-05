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
        Schema::create('inv_motors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_id')->constrained('inventaris')->onDelete('cascade');
            $table->string('tahun_pembuatan', 4)->nullable();
            $table->string('tahun_rakit', 4)->nullable();
            $table->string('warna', 30)->nullable();
            $table->string('no_rangka', 50)->nullable();
            $table->string('no_mesin', 50)->nullable();
            $table->string('no_bpkb', 50)->nullable();
            $table->string('no_polisi', 20)->nullable();
            $table->date('tgl_pajak')->nullable();
            $table->string('atas_nama', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_motors');
    }
};
