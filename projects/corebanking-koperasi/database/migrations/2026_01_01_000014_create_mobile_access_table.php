<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cif_id')->constrained('cifs')->onDelete('cascade');
            $table->string('cif_no', 20)->unique();
            $table->string('username', 50)->nullable()->unique();
            $table->string('password_hash')->nullable();
            $table->string('pin_hash')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->string('device_id', 255)->nullable();
            $table->string('fcm_token', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('wrong_pin_count')->default(0);
            $table->timestamp('pin_blocked_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('cif_no');
            $table->index('is_active');
        });

        Schema::create('mobile_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobile_access_id')->constrained('mobile_access')->onDelete('cascade');
            $table->string('token', 80)->unique();
            $table->string('device_id', 255)->nullable();
            $table->string('device_name', 100)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index('token');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_tokens');
        Schema::dropIfExists('mobile_access');
    }
};
