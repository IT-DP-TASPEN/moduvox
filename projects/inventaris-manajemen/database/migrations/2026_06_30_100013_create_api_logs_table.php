<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('api_journals')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('endpoint', 255)->nullable();
            $table->string('method', 10)->default('POST');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->integer('http_status')->nullable();
            $table->integer('duration_ms')->nullable()->comment('Response time in milliseconds');
            $table->timestamps();

            $table->index('journal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
