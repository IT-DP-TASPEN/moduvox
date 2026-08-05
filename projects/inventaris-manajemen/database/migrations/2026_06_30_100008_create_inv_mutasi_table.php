<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_mutasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_id')->constrained('inventaris')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('kantor_asal_id')->constrained('mst_kantor')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('kantor_tujuan_id')->constrained('mst_kantor')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('ruangan_asal_id')->nullable()->constrained('mst_ruangan')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('ruangan_tujuan_id')->nullable()->constrained('mst_ruangan')->cascadeOnUpdate()->nullOnDelete();
            $table->date('tgl_mutasi');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index('inventaris_id');
            $table->index('tgl_mutasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_mutasi');
    }
};
