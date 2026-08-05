<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->decimal('flagging_fee', 20, 2)->default(0)->after('insurance_fee');
            $table->decimal('stamp_duty_fee', 20, 2)->default(0)->after('flagging_fee');
            $table->integer('prepaid_installment_count')->default(0)->after('stamp_duty_fee');
            $table->decimal('prepaid_installment_amount', 20, 2)->default(0)->after('prepaid_installment_count');
            $table->integer('blocked_savings_count')->default(0)->after('prepaid_installment_amount');
            $table->decimal('blocked_savings_amount', 20, 2)->default(0)->after('blocked_savings_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'flagging_fee',
                'stamp_duty_fee',
                'prepaid_installment_count',
                'prepaid_installment_amount',
                'blocked_savings_count',
                'blocked_savings_amount',
            ]);
        });
    }
};
