<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('district')) {
            return;
        }

        Schema::create('district', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('regency_id')->index();
            $table->unsignedBigInteger('province_id')->index();
            $table->string('nama');
            $table->timestamps();

            $table->index(['province_id', 'regency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('district');
    }
};
