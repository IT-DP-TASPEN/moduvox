<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('loan_products', 'deferred_interest_coa_id')) {
            Schema::table('loan_products', function (Blueprint $table) {
                $table->foreignId('deferred_interest_coa_id')
                    ->nullable()
                    ->after('interest_revenue_coa_id')
                    ->constrained('coas');
            });
        }

        $deferredCoaId = DB::table('coas')->where('coa_code', '219020')->value('id');
        if ($deferredCoaId) {
            DB::table('loan_products')
                ->where('is_diskonto', true)
                ->whereNull('deferred_interest_coa_id')
                ->update(['deferred_interest_coa_id' => $deferredCoaId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('loan_products', 'deferred_interest_coa_id')) {
            Schema::table('loan_products', function (Blueprint $table) {
                $table->dropForeign(['deferred_interest_coa_id']);
                $table->dropColumn('deferred_interest_coa_id');
            });
        }
    }
};
