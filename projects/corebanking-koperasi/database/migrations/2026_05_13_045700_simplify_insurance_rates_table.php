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
        Schema::table('insurance_rates', function (Blueprint $table) {
            $table->renameColumn('min_age', 'age');
            $table->dropColumn('max_age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurance_rates', function (Blueprint $table) {
            $table->renameColumn('age', 'min_age');
            $table->integer('max_age')->default(100)->after('min_age');
        });
    }
};
