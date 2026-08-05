<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_configs', function (Blueprint $table) {
            $table->id();
            $table->string('module_key');
            $table->string('action');
            $table->boolean('is_active')->default(false);
            $table->json('authorized_roles')->nullable();
            $table->timestamps();
            $table->unique(['module_key', 'action']);
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('module_key');
            $table->string('model_id')->nullable();
            $table->string('action');
            $table->json('data_before')->nullable();
            $table->json('data_after')->nullable();
            $table->string('status')->default('PENDING');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_configs');
    }
};
