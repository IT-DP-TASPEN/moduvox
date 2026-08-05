<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('penyusutan_batch')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('reff_id', 50)->unique()->comment('Format: IV-KKYYMMCCC');
            $table->json('payload')->nullable();
            $table->string('state', 20)->default('DRAFT');
            $table->string('core_reff', 100)->nullable()->comment('Response ID dari MGate');
            $table->text('response_body')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index('reff_id');
            $table->index('state');
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_journals');
    }
};
