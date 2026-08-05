<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_providers', function (Blueprint $table) {
            $table->id();
            $table->string('provider_code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('insurance_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_provider_id')->constrained('insurance_providers')->cascadeOnDelete();
            $table->string('product_code')->unique();
            $table->string('name');
            $table->string('type')->default('JIWA'); // JIWA, KENDARAAN, BANGUNAN, KREDIT
            $table->decimal('premium_rate', 5, 2)->default(0); // in percentage
            $table->string('calculation_base')->default('PLAFOND'); // PLAFOND, OUSTANDING
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_products');
        Schema::dropIfExists('insurance_providers');
    }
};
