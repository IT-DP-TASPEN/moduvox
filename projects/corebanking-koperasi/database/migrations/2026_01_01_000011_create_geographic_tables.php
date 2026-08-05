<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
            $table->index('nama');
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->string('nama');
            $table->string('dati2');
            $table->timestamps();
            $table->index(['province_id', 'nama']);
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->foreignId('regency_id')->constrained('cities')->onDelete('cascade');
            $table->string('nama');
            $table->timestamps();
            $table->index(['regency_id', 'nama']);
        });

        Schema::create('subdistricts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->foreignId('regency_id')->constrained('cities')->onDelete('cascade');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->string('nama');
            $table->timestamps();
            $table->index(['district_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subdistricts');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
    }
};
