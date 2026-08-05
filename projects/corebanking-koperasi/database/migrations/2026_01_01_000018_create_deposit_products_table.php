<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);

            // Term (Tenor) Rules
            $table->integer('min_term')->default(1);
            $table->integer('max_term')->nullable();
            $table->string('term_unit')->default('MONTH'); // MONTH, DAY

            // Amount Rules
            $table->decimal('min_amount', 20, 2)->default(0);
            $table->decimal('max_amount', 20, 2)->nullable();

            // Interest & Calculation
            $table->decimal('min_interest_rate', 5, 2)->default(0);
            $table->decimal('max_interest_rate', 5, 2)->default(0);
            $table->string('interest_period')->default('MONTHLY');
            $table->string('interest_calculation_type')->default('FLAT'); // FLAT, ANNUITY, etc.
            $table->decimal('tax_rate', 5, 2)->default(0);

            // Accounting Rules (COA Mapping)
            $table->foreignId('liability_coa_id')->nullable()->constrained('coas');
            $table->foreignId('interest_expense_coa_id')->nullable()->constrained('coas');
            $table->foreignId('accrued_interest_payable_coa_id')->nullable()->constrained('coas');
            $table->foreignId('tax_liability_coa_id')->nullable()->constrained('coas');
            $table->foreignId('admin_fee_revenue_coa_id')->nullable()->constrained('coas');
            $table->foreignId('interest_payable_coa_id')->nullable()->constrained('coas');
            $table->foreignId('default_cash_coa_id')->nullable()->constrained('coas');
            $table->foreignId('default_bank_coa_id')->nullable()->constrained('coas');
            $table->foreignId('kas_coa_id')->nullable()->constrained('coas');
            $table->foreignId('aba_transit_coa_id')->nullable()->constrained('coas');

            // Governance
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_products');
    }
};
