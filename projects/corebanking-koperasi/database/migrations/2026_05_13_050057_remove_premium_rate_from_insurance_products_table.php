<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('insurance_products', function (Blueprint $table) {
            $table->dropColumn('premium_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurance_products', function (Blueprint $table) {
            $table->decimal('premium_rate', 8, 4)->default(0);
        });
    }
};
