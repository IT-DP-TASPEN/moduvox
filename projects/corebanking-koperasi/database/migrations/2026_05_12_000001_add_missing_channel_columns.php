<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing columns required by operation services:
 *
 *  loan_transactions   → channel (CASH | ABA | INTERNAL)
 *  deposit_accounts    → fund_channel, closed_at
 *  deposit_transactions→ channel
 *  deposit_schedules   → paid_at
 */
return new class extends Migration {
    public function up(): void
    {
        // ── loan_transactions ────────────────────────────────────────────────
        if (!Schema::hasColumn('loan_transactions', 'channel')) {
            Schema::table('loan_transactions', function (Blueprint $table) {
                $table->string('channel')->default('CASH')->after('transaction_type')
                    ->comment('CASH | ABA | INTERNAL');
            });
        }

        // ── deposit_accounts ─────────────────────────────────────────────────
        Schema::table('deposit_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('deposit_accounts', 'fund_channel')) {
                $table->string('fund_channel')->change();
            } else {
                $table->string('fund_channel')->default('CASH')->after('rollover_type')
                    ->comment('Channel sumber dana penempatan: CASH | ABA | INTERNAL');
            }
            if (!Schema::hasColumn('deposit_accounts', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('status');
            }
        });

        // ── deposit_transactions ─────────────────────────────────────────────
        if (!Schema::hasColumn('deposit_transactions', 'channel')) {
            Schema::table('deposit_transactions', function (Blueprint $table) {
                $table->string('channel')->default('CASH')->after('type')
                    ->comment('CASH | ABA | INTERNAL');
            });
        }

        // ── deposit_schedules ────────────────────────────────────────────────
        if (!Schema::hasColumn('deposit_schedules', 'paid_at')) {
            Schema::table('deposit_schedules', function (Blueprint $table) {
                $table->timestamp('paid_at')->nullable()->after('payment_date');
            });
        }
    }

    public function down(): void
    {
        // loan_transactions
        if (Schema::hasColumn('loan_transactions', 'channel')) {
            Schema::table('loan_transactions', fn(Blueprint $t) => $t->dropColumn('channel'));
        }

        // deposit_accounts
        Schema::table('deposit_accounts', function (Blueprint $table) {
            foreach (['fund_channel', 'closed_at'] as $col) {
                if (Schema::hasColumn('deposit_accounts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // deposit_transactions
        if (Schema::hasColumn('deposit_transactions', 'channel')) {
            Schema::table('deposit_transactions', fn(Blueprint $t) => $t->dropColumn('channel'));
        }

        // deposit_schedules
        if (Schema::hasColumn('deposit_schedules', 'paid_at')) {
            Schema::table('deposit_schedules', fn(Blueprint $t) => $t->dropColumn('paid_at'));
        }
    }
};
