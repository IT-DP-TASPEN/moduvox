<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);

            // Interest & Calculation
            $table->string('interest_calculation_type')->default('DAILY_BALANCE');
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->string('interest_payment_period')->default('MONTHLY');

            // Limits & Rules
            $table->integer('min_initial_deposit')->default(0);
            $table->integer('min_balance')->default(0);
            $table->integer('max_balance')->nullable();
            $table->boolean('has_overdraft')->default(false);

            // Fees
            $table->boolean('has_admin_fee')->default(false);
            $table->integer('admin_fee')->default(0);
            $table->boolean('has_closing_fee')->default(false);
            $table->integer('closed_fee')->default(0);
            $table->integer('min_balance_penalty')->default(0);
            $table->integer('min_balance_penalty_period')->nullable();

            // Dormancy
            $table->boolean('has_automatic_dormant')->default(false);
            $table->integer('no_transaction_monthly_terms')->nullable();
            $table->integer('no_transaction_penalty')->default(0);
            $table->integer('dormant_penalty_grace_period')->nullable();
            $table->integer('dormant_penalty_amount')->default(0);

            // Tax
            $table->boolean('has_tax_on_interest')->default(false);
            $table->decimal('tax_rate', 5, 2)->default(0);

            // Accounting (COA Mappings)
            $table->foreignId('liability_coa_id')->nullable()->constrained('coas');
            $table->foreignId('interest_expense_coa_id')->nullable()->constrained('coas');
            $table->foreignId('admin_fee_revenue_coa_id')->nullable()->constrained('coas');
            $table->foreignId('tax_liability_coa_id')->nullable()->constrained('coas');
            $table->foreignId('accrued_interest_payable_coa_id')->nullable()->constrained('coas');
            $table->foreignId('interest_payable_coa_id')->nullable()->constrained('coas');
            $table->foreignId('default_cash_coa_id')->nullable()->constrained('coas');
            $table->foreignId('default_bank_coa_id')->nullable()->constrained('coas');
            $table->foreignId('penalty_revenue_coa_id')->nullable()->constrained('coas');
            $table->foreignId('aba_transit_coa_id')->nullable()->constrained('coas');

            $table->string('fee_name')->nullable();
            $table->integer('fee_amount')->nullable();
            $table->string('fee_type')->nullable();

            // Governance
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_products');
    }
};
