<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_bilyets', function (Blueprint $table) {
            $table->id();
            $table->string('bilyet_number')->unique();
            $table->string('kode_bilyet');
            $table->integer('sequence');
            $table->enum('status', ['AVAILABLE', 'USED', 'CANCELLED', 'LOST'])->default('AVAILABLE');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'branch_id']);
            $table->index('bilyet_number');
        });

        Schema::create('deposit_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_no')->unique();
            $table->foreignId('cif_id')->constrained('cifs');
            $table->foreignId('deposit_product_id')->constrained('deposit_products');
            $table->foreignId('deposit_bilyet_id')->nullable()->constrained('deposit_bilyets');
            
            $table->decimal('amount', 20, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->integer('tenor'); // In months
            
            $table->date('placement_date');
            $table->date('maturity_date');
            
            $table->enum('rollover_type', ['NONE', 'PRINCIPAL', 'PRINCIPAL_INTEREST'])->default('NONE');
            $table->foreignId('saving_account_id')->nullable()->constrained('saving_accounts'); // For interest payout
            $table->string('interest_calculation_type')->default('MONTHLY');
            
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('marketing_id')->nullable()->constrained('marketing_masters')->nullOnDelete();
            
            $table->string('source_of_funds')->nullable();
            $table->enum('fund_channel', ['KAS', 'BANK'])->default('BANK');
            $table->string('reason')->nullable();
            
            $table->enum('status', ['PENDING', 'ACTIVE', 'CLOSED', 'MATURED'])->default('PENDING');
            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('deposit_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->foreignId('deposit_account_id')->constrained('deposit_accounts');
            $table->dateTime('transaction_date');
            $table->enum('type', ['PLACEMENT', 'ROLLOVER', 'INTEREST_PAYMENT', 'WITHDRAWAL', 'PENALTY', 'REVERSAL']);
            $table->enum('channel', ['CASH', 'ABA', 'INTERNAL'])->default('CASH');
            $table->decimal('amount', 20, 2);
            $table->foreignId('journal_id')->nullable()->constrained('journals');
            $table->string('reference_no')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('deposit_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deposit_account_id')->constrained()->onDelete('cascade');
            $table->integer('month_index');
            $table->date('schedule_date');
            $table->decimal('gross_interest', 20, 2);
            $table->decimal('tax_amount', 20, 2);
            $table->decimal('net_interest', 20, 2);
            $table->string('status')->default('PENDING'); // PENDING, PAID
            $table->datetime('payment_date')->nullable();
            $table->foreignId('deposit_transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index(['deposit_account_id', 'status']);
            $table->index('schedule_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_schedules');
        Schema::dropIfExists('deposit_transactions');
        Schema::dropIfExists('deposit_accounts');
        Schema::dropIfExists('deposit_bilyets');
    }
};
