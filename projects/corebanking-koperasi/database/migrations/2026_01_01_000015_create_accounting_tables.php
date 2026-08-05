<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. COA (Chart of Accounts) ────────────────────────────────────────
        Schema::create('coas', function (Blueprint $table) {
            $table->id();
            $table->string('coa_code')->unique();
            $table->string('name');
            $table->enum('type', ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE']);
            $table->foreignId('parent_id')->nullable()->constrained('coas')->onDelete('cascade');
            $table->boolean('is_leaf')->default(true);
            $table->boolean('is_cash')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 2. Journals ───────────────────────────────────────────────────────
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('transaction_date');
            $table->string('reference_no')->unique();
            $table->string('description')->nullable();
            $table->text('revision_notes')->nullable();
            $table->enum('journal_type', ['SYSTEM', 'MANUAL', 'REVERSAL'])->default('SYSTEM');
            $table->unsignedBigInteger('original_journal_id')->nullable();
            $table->boolean('is_revision')->default(false);
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('original_journal_id')->references('id')->on('journals')->nullOnDelete();
        });

        // ── 3. Journal Entries (Double-Entry lines) ───────────────────────────
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals')->onDelete('cascade');
            $table->foreignId('coa_id')->constrained('coas');
            $table->decimal('debit', 20, 2)->default(0);
            $table->decimal('credit', 20, 2)->default(0);
            $table->timestamps();
        });

        // ── 4. COA Movements (Balance Tracking per Branch per Day) ────────────
        Schema::create('coa_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('coa_id')->constrained('coas');
            $table->date('transaction_date');
            $table->decimal('starting_balance', 20, 2)->default(0);
            $table->decimal('debit', 20, 2)->default(0);
            $table->decimal('credit', 20, 2)->default(0);
            $table->decimal('ending_balance', 20, 2)->default(0);
            $table->timestamps();
            $table->index(['coa_id', 'branch_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_movements');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('journals');
        Schema::dropIfExists('coas');
    }
};
