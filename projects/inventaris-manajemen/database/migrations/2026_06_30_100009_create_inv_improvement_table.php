<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_improvement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_id')->constrained('inventaris')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('nilai_tambah', 15, 2);
            $table->date('tgl_efektif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index('inventaris_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_improvement');
    }
};
