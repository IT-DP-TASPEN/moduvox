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
        Schema::table('master_shus', function (Blueprint $table) {
            $table->foreignId('saving_account_id')->nullable()->constrained('saving_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_shus', function (Blueprint $table) {
            $table->dropForeign(['saving_account_id']);
            $table->dropColumn('saving_account_id');
        });
    }
};
