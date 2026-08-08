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
        Schema::create('marketing_masters', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->unique();
            $table->text('marketing_name');
            $table->text('jabatan');
            $table->foreignId('branch_master_id')->constrained('branch_masters')->cascadeOnDelete();
            $table->text(column: 'lokasi_open_table');
            $table->text('jenis_marketing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_masters');
    }
};
