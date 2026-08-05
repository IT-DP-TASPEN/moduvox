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
        Schema::create('inv_tanahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_id')->constrained('inventaris')->onDelete('cascade');
            $table->string('no_shm', 50)->nullable();
            $table->string('no_shgb', 50)->nullable();
            $table->date('tanggal_shm')->nullable();
            $table->string('surat_ukur', 50)->nullable();
            $table->string('luas_tanah', 10)->nullable();
            $table->string('luas_bangunan', 10)->nullable();
            $table->string('atas_nama', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_tanahs');
    }
};
