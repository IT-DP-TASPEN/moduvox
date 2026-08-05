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
        Schema::create('shu_distribution_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shu_distribution_id')->constrained()->onDelete('cascade');
            $table->string('kriteria');
            $table->decimal('persentase', 5, 2)->default(0);
            $table->decimal('total_shu', 20, 2)->default(0);
            $table->integer('jumlah_orang')->default(0);
            $table->decimal('nominal_per_orang', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shu_distribution_details');
    }
};
