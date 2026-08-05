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
        Schema::table('loan_products', function (Blueprint $table) {
            $table->foreignId('flagging_revenue_coa_id')->nullable()->constrained('coas')->after('insurance_revenue_coa_id');
            $table->foreignId('stamp_duty_payable_coa_id')->nullable()->constrained('coas')->after('flagging_revenue_coa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropForeign(['flagging_revenue_coa_id']);
            $table->dropForeign(['stamp_duty_payable_coa_id']);
            $table->dropColumn(['flagging_revenue_coa_id', 'stamp_duty_payable_coa_id']);
        });
    }
};
