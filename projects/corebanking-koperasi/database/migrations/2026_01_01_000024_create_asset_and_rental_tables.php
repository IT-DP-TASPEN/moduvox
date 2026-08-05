<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('asset_categories')->nullOnDelete();

            $table->string('depreciation_method')->nullable();
            $table->integer('useful_life_years')->nullable();
            $table->decimal('depreciation_rate', 5, 2)->nullable();

            $table->foreignId('asset_coa_id')->nullable()->constrained('coas')->nullOnDelete();
            $table->foreignId('accumulated_depreciation_coa_id')->nullable()->constrained('coas')->nullOnDelete();
            $table->foreignId('depreciation_expense_coa_id')->nullable()->constrained('coas')->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->foreignId('asset_category_id')->constrained('asset_categories');
            $table->foreignId('branch_id')->constrained('branches');

            $table->date('purchase_date');
            $table->decimal('purchase_price', 20, 2);
            $table->decimal('current_value', 20, 2);
            $table->decimal('accumulated_depreciation', 20, 2)->default(0);

            $table->string('depreciation_method'); // STRAIGHT_LINE, DECLINING_BALANCE, NONE
            $table->integer('useful_life_years');
            $table->decimal('depreciation_rate', 5, 2)->nullable();

            $table->enum('status', ['ACTIVE', 'DISPOSED', 'LOST', 'SOLD', 'RENTED'])->default('ACTIVE');
            $table->text('location')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('period_year_month', 7); // Format: YYYY-MM
            $table->date('depreciation_date');
            $table->decimal('depreciation_amount', 20, 2);
            $table->decimal('accumulated_depreciation_after', 20, 2);
            $table->decimal('book_value_after', 20, 2);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['asset_id', 'period_year_month']);
        });

        Schema::create('rekanan', function (Blueprint $table) {
            $table->id();
            $table->string('rekanan_code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('npwp')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('asset_rentals', function (Blueprint $table) {
            $table->id();
            $table->string('contract_no')->unique();
            $table->foreignId('asset_id')->constrained('assets');
            $table->foreignId('rekanan_id')->constrained('rekanan');
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('rental_start_date');
            $table->date('rental_end_date');
            $table->decimal('monthly_rate', 20, 2);
            $table->unsignedTinyInteger('payment_due_day')->default(1);
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'TERMINATED'])->default('ACTIVE');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('asset_rental_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_rental_id')->constrained('asset_rentals')->onDelete('cascade');
            $table->string('billing_period', 7); // Format: YYYY-MM
            $table->date('billing_date');
            $table->date('due_date');
            $table->decimal('amount', 20, 2);
            $table->enum('status', ['UNPAID', 'PAID', 'OVERDUE'])->default('UNPAID');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['asset_rental_id', 'billing_period'], 'unique_rental_billing_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_rental_billings');
        Schema::dropIfExists('asset_rentals');
        Schema::dropIfExists('rekanan');
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
