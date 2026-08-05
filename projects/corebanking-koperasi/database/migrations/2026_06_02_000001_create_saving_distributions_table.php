<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('distribution_no')->unique();

            // Type: CREDIT (kredit ke rekening) atau DEBIT (debit dari rekening)
            $table->enum('distribution_type', ['CREDIT', 'DEBIT']);

            // Produk tabungan yang ditarget
            $table->foreignId('saving_product_id')->constrained('saving_products');

            // COA Lawan (sumber dana untuk CREDIT, atau tujuan dana untuk DEBIT)
            $table->foreignId('counterpart_coa_id')->constrained('coas');

            // Nominal
            $table->decimal('amount_per_account', 20, 2)->nullable();
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->integer('account_count')->default(0);

            // Info distribusi
            $table->string('description')->nullable();
            $table->date('effective_date');

            // Status workflow
            $table->enum('status', ['DRAFT', 'PENDING', 'EXECUTED', 'CANCELLED'])->default('DRAFT');

            // Eksekusi
            $table->foreignId('journal_id')->nullable()->constrained('journals');
            $table->timestamp('executed_at')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('users');

            // Governance
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('saving_distribution_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saving_distribution_id')->constrained('saving_distributions')->onDelete('cascade');
            $table->foreignId('saving_account_id')->constrained('saving_accounts');
            $table->decimal('amount', 20, 2);
            $table->decimal('balance_before', 20, 2)->default(0);
            $table->decimal('balance_after', 20, 2)->default(0);
            $table->string('status')->default('PENDING'); // PENDING, SUCCESS, FAILED
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_distribution_details');
        Schema::dropIfExists('saving_distributions');
    }
};
