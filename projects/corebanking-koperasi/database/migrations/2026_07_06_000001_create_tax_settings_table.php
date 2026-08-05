<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->enum('calculation_base', ['TOTAL_REVENUE', 'PROFIT_BEFORE_TAX'])->default('TOTAL_REVENUE');
            $table->foreignId('expense_coa_id')->constrained('coas');
            $table->foreignId('payable_coa_id')->constrained('coas');
            $table->date('effective_from')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};
