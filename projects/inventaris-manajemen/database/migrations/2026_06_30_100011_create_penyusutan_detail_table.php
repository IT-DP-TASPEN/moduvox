<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyusutan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('penyusutan_batch')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('inventaris_id')->constrained('inventaris')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedBigInteger('kantor_id')->comment('Denormalized: cabang saat penyusutan');
            $table->decimal('beban_bulan_ini', 15, 2)->default(0);
            $table->decimal('nilai_buku_sebelum', 15, 2)->default(0);
            $table->decimal('nilai_buku_sesudah', 15, 2)->default(0);
            $table->decimal('akumulasi', 15, 2)->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index('batch_id');
            $table->index('inventaris_id');
            $table->index('kantor_id');
            $table->foreign('kantor_id')->references('id')->on('mst_kantor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyusutan_detail');
    }
};
