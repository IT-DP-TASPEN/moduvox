<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_no')->unique(); // Branch + Product + 11-digit seq
            $table->foreignId('cif_id')->constrained('cifs');
            $table->foreignId('saving_product_id')->constrained('saving_products');
            $table->foreignId('branch_id')->constrained('branches');

            $table->decimal('balance', 20, 2)->default(0);
            $table->decimal('blocked_balance', 20, 2)->default(0);
            $table->enum('status', ['PENDING', 'ACTIVE', 'BLOCKED', 'DORMANT', 'CLOSED'])->default('ACTIVE');

            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['cif_id', 'status']);
        });

        Schema::create('saving_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->foreignId('saving_account_id')->constrained('saving_accounts');
            $table->date('transaction_date');

            $table->enum('type', [
                'DEPOSIT',
                'WITHDRAWAL',
                'TRANSFER_IN',
                'TRANSFER_OUT',
                'INTEREST',
                'TAX',
                'FEE',
                'REVERSAL',
                'BLOCK',
                'UNBLOCK'
            ]);

            $table->enum('channel', ['CASH', 'ABA', 'INTERNAL'])->default('CASH');
            $table->decimal('amount', 20, 2);
            $table->decimal('balance_after', 20, 2);

            $table->foreignId('journal_id')->nullable()->constrained('journals');
            $table->string('reference_no')->unique();
            $table->string('description')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['saving_account_id', 'transaction_date']);
        });

        Schema::create('saving_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saving_account_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 20, 2);
            $table->string('reference_no')->unique();
            $table->string('reason')->nullable();
            $table->enum('status', ['ACTIVE', 'RELEASED'])->default('ACTIVE');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('released_by')->nullable()->constrained('users');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_blocks');
        Schema::dropIfExists('saving_transactions');
        Schema::dropIfExists('saving_accounts');
    }
};
