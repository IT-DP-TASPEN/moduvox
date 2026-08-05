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
        Schema::create('insurance_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_product_id')->constrained()->cascadeOnDelete();
            $table->integer('min_age')->default(0);
            $table->integer('max_age')->default(100);
            $table->integer('tenor_months')->default(12);
            $table->decimal('rate', 8, 4)->default(0); // e.g. 0.0125 for 1.25%
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_rates');
    }
};
