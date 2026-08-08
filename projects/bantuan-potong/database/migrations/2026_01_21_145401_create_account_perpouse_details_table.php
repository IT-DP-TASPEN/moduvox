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
        Schema::create('account_perpouse_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_perpouse_master_id')->constrained('account_perpouse_masters')->cascadeOnDelete();
            $table->text('detail_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_perpouse_details');
    }
};
