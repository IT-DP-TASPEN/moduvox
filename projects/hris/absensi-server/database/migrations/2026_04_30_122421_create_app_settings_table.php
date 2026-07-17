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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed initial data
        \Illuminate\Support\Facades\DB::table('app_settings')->insert([
            ['key' => 'android_download_url', 'value' => '#', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'ios_download_url', 'value' => '#', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_version', 'value' => '1.0.0', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
