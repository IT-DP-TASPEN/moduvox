<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_no')->nullable()->unique();
            $table->string('pk_number')->nullable()->unique(); // Nomor Perjanjian Kredit
            $table->foreignId('cif_id')->constrained('cifs');
            $table->foreignId('loan_product_id')->constrained('loan_products');
            $table->foreignId('saving_account_id')->nullable()->constrained('saving_accounts'); // Rekening pencairan & auto-debet
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('marketing_id')->nullable()->constrained('marketing_masters')->nullOnDelete();

            // Principal & Calculation
            $table->decimal('principal_amount', 20, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('interest_margin', 5, 2)->default(0); // For specific calculation methods
            $table->integer('tenor'); // in months usually
            $table->string('tenor_type')->default('MONTHS');
            $table->string('calculation_method')->default('FLAT');
            $table->integer('due_date_cycle')->default(1); // Tanggal jatuh tempo angsuran
            $table->date('disbursement_date')->nullable();

            // Balances
            $table->decimal('outstanding_principal', 20, 2)->default(0);
            $table->decimal('outstanding_interest', 20, 2)->default(0);
            $table->decimal('outstanding_penalty', 20, 2)->default(0);

            $table->unsignedInteger('dpd_days')->default(0);
            $table->unsignedTinyInteger('kol_level')->default(1);

            $table->string('collateral_type')->nullable(); // KL1, KL2, dll
            $table->text('reason')->nullable();

            // Data Pemohon
            $table->string('applicant_purpose')->nullable();
            $table->string('applicant_occupation')->nullable();
            $table->string('applicant_company_name')->nullable();
            $table->string('applicant_company_address')->nullable();
            $table->decimal('applicant_monthly_income', 20, 2)->nullable();
            $table->decimal('applicant_monthly_expense', 20, 2)->nullable();
            $table->decimal('applicant_other_income', 20, 2)->nullable();

            // Data Agunan
            $table->string('collateral_description')->nullable();
            $table->string('collateral_certificate_no')->nullable();
            $table->decimal('collateral_value', 20, 2)->nullable();
            $table->string('collateral_address')->nullable();

            // Data Penjamin
            $table->string('guarantor_name')->nullable();
            $table->string('guarantor_nik')->nullable();
            $table->string('guarantor_phone')->nullable();
            $table->string('guarantor_address')->nullable();
            $table->string('guarantor_relation')->nullable();

            // Biaya & Provisi
            $table->decimal('provision_fee', 20, 2)->nullable()->default(0);
            $table->decimal('admin_fee', 20, 2)->nullable()->default(0);
            $table->decimal('insurance_fee', 20, 2)->nullable()->default(0);
            $table->decimal('notary_fee', 20, 2)->nullable()->default(0);

            // Status & Analisa
            $table->string('analyst_notes')->nullable();
            $table->string('analyst_recommendation')->nullable(); // APPROVE, REJECT, REVIEW
            $table->enum('status', ['PENDING', 'APPROVED', 'ACTIVE', 'CLOSED', 'NPL', 'CANCELLED', 'REJECTED'])->default('PENDING');

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_account_id')->constrained('loan_accounts')->onDelete('cascade');
            $table->integer('installment_number');
            $table->date('due_date');

            $table->decimal('principal_amount', 20, 2)->default(0);
            $table->decimal('interest_amount', 20, 2)->default(0);
            $table->decimal('penalty_amount', 20, 2)->default(0);

            $table->decimal('principal_paid', 20, 2)->default(0);
            $table->decimal('interest_paid', 20, 2)->default(0);
            $table->decimal('penalty_paid', 20, 2)->default(0);

            $table->enum('status', ['UNPAID', 'PARTIAL', 'PAID', 'VOID'])->default('UNPAID');

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['loan_account_id', 'status']);
        });

        Schema::create('loan_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_account_id')->constrained('loan_accounts')->onDelete('cascade');
            $table->string('reference_number')->unique();
            $table->enum('transaction_type', ['DISBURSEMENT', 'REPAYMENT_MANUAL', 'REPAYMENT_AUTO', 'REPAYMENT_SETTLEMENT', 'PENALTY', 'REVERSAL']);
            $table->enum('channel', ['CASH', 'ABA', 'INTERNAL'])->default('INTERNAL');

            $table->foreignId('reversed_by_transaction_id')->nullable()->constrained('loan_transactions');

            $table->decimal('amount_principal', 20, 2)->default(0);
            $table->decimal('amount_interest', 20, 2)->default(0);
            $table->decimal('amount_penalty', 20, 2)->default(0);
            $table->decimal('amount_admin_fee', 20, 2)->default(0);
            $table->decimal('amount_provision', 20, 2)->default(0);
            $table->decimal('amount_insurance_fee', 20, 2)->default(0);
            $table->decimal('amount_notary_fee', 20, 2)->default(0);
            $table->decimal('total_amount', 20, 2)->default(0);

            $table->foreignId('journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->text('description')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('loan_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_account_id')->constrained('loan_accounts')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('document_name');
            $table->string('file_path');
            $table->string('file_original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('status')->default('PENDING');
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_documents');
        Schema::dropIfExists('loan_transactions');
        Schema::dropIfExists('loan_schedules');
        Schema::dropIfExists('loan_accounts');
    }
};
