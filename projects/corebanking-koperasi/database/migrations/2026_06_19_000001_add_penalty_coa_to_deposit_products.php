<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposit_products', function (Blueprint $table) {
            $table->foreignId('penalty_revenue_coa_id')->nullable()->constrained('coas')->after('aba_transit_coa_id');
        });
    }

    public function down(): void
    {
        Schema::table('deposit_products', function (Blueprint $table) {
            $table->dropForeign(['penalty_revenue_coa_id']);
            $table->dropColumn('penalty_revenue_coa_id');
        });
    }
};
