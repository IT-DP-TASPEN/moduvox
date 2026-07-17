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
        Schema::create('position_allowance_masters', function (Blueprint $table) {
            $table->id();
            $table->string('position_name')->unique();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('max_performance_allowance', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_allowance_masters');
    }
};
