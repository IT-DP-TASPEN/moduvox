<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branch_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_master_id')->constrained('mitra_masters')->cascadeOnDelete();
            $table->string('branch_code');
            $table->text('branch_name');
            $table->index('branch_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_masters');
    }
};