<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('loan_schedules', 'payment_date')) {
            Schema::table('loan_schedules', function (Blueprint $table) {
                $table->timestamp('payment_date')->nullable()->after('status');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE loan_accounts MODIFY status ENUM(
                    'PENDING',
                    'APPROVED',
                    'ACTIVE',
                    'CLOSED',
                    'NPL',
                    'CANCELLED',
                    'REJECTED',
                    'CLAIM_SUBMITTED',
                    'CLAIM_APPROVED'
                ) NOT NULL DEFAULT 'PENDING'"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE loan_accounts MODIFY status ENUM(
                    'PENDING',
                    'APPROVED',
                    'ACTIVE',
                    'CLOSED',
                    'NPL',
                    'CANCELLED',
                    'REJECTED'
                ) NOT NULL DEFAULT 'PENDING'"
            );
        }

        if (Schema::hasColumn('loan_schedules', 'payment_date')) {
            Schema::table('loan_schedules', function (Blueprint $table) {
                $table->dropColumn('payment_date');
            });
        }
    }
};
