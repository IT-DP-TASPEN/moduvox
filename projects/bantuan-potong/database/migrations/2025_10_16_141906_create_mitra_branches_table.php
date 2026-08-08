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
        Schema::create('mitra_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_master_id')->constrained('mitra_masters')->cascadeOnDelete();
            $table->string('nama_cabang');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_branches');
    }
};