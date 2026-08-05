<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_account_id')->constrained('loan_accounts')->cascadeOnDelete();
            $table->foreignId('insurance_product_id')->constrained('insurance_products');
            $table->string('policy_no')->nullable()->unique();
            $table->string('certificate_no')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('coverage_amount', 20, 2)->default(0);
            $table->decimal('premium_amount', 20, 2)->default(0);
            $table->string('status')->default('ACTIVE');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['loan_account_id', 'status']);
        });

        Schema::create('insurance_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_no')->unique();
            $table->foreignId('loan_account_id')->constrained('loan_accounts')->cascadeOnDelete();
            $table->foreignId('loan_insurance_policy_id')->constrained('loan_insurance_policies')->cascadeOnDelete();
            $table->date('incident_date')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('approval_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('claim_amount', 20, 2)->default(0);
            $table->decimal('approved_amount', 20, 2)->default(0);
            $table->decimal('paid_amount', 20, 2)->default(0);
            $table->string('status')->default('SUBMITTED');
            $table->text('remarks')->nullable();
            $table->foreignId('recognition_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('payment_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['loan_account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('loan_insurance_policies');
    }
};
