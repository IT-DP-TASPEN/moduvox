<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('deposit_products', 'tax_rate')) {
            return;
        }

        Schema::table('deposit_products', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0)->after('interest_calculation_type');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('deposit_products', 'tax_rate')) {
            return;
        }

        Schema::table('deposit_products', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
        });
    }
};
