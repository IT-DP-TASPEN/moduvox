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
        Schema::create('notas_ownerships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_master_id')
                ->constrained('mitra_masters')
                ->cascadeOnDelete();
            $table->string('notas')->unique();
            $table->string('nama_nasabah');
            $table->string('rek_tabungan');
            $table->string('rek_replace');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_ownerships');
    }
};
