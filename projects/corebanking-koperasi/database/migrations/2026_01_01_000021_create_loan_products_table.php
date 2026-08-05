<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);

            // Calculation rules
            $table->string('calculation_method')->default('FLAT'); // FLAT, EFFECTIVE, ANNUITY
            $table->decimal('interest_rate_min', 5, 2)->default(0);
            $table->decimal('interest_rate_max', 5, 2)->default(0);

            // Fees & Rates
            $table->decimal('provision_rate', 5, 2)->default(0); // % provisi
            $table->decimal('admin_fee', 20, 2)->default(0);     // nominal admin
            $table->decimal('penalty_rate', 5, 2)->default(0);   // % denda per hari/bulan keterlambatan
            $table->decimal('insurance_rate', 5, 2)->default(0); // % asuransi
            $table->decimal('notary_fee', 20, 2)->default(0);    // nominal notaris

            $table->foreignId('insurance_product_id')->nullable()->constrained('insurance_products')->nullOnDelete();

            // Tenor Rules
            $table->integer('tenor_min')->default(1);
            $table->integer('tenor_max')->default(120);
            $table->string('tenor_type')->default('MONTHS'); // MONTHS, WEEKS, DAYS

            // Accounting (COA Mapping)
            $table->foreignId('principal_coa_id')->nullable()->constrained('coas');
            $table->foreignId('accrued_interest_coa_id')->nullable()->constrained('coas');
            $table->foreignId('accrued_interest_receivable_coa_id')->nullable()->constrained('coas');
            $table->foreignId('interest_revenue_coa_id')->nullable()->constrained('coas');
            $table->foreignId('provision_revenue_coa_id')->nullable()->constrained('coas');
            $table->foreignId('admin_fee_revenue_coa_id')->nullable()->constrained('coas');
            $table->foreignId('insurance_revenue_coa_id')->nullable()->constrained('coas');
            $table->foreignId('notary_revenue_coa_id')->nullable()->constrained('coas');
            $table->foreignId('penalty_revenue_coa_id')->nullable()->constrained('coas');
            $table->foreignId('default_cash_coa_id')->nullable()->constrained('coas');
            $table->foreignId('default_bank_coa_id')->nullable()->constrained('coas');
            $table->foreignId('ckpn_coa_id')->nullable()->constrained('coas');
            $table->foreignId('suspense_coa_id')->nullable()->constrained('coas');
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
        Schema::dropIfExists('loan_products');
    }
};
