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
        Schema::table('saving_account_internals', function (Blueprint $table) {
            $table->foreignId('mitra_master_id')->nullable()->constrained('mitra_masters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saving_account_internals', function (Blueprint $table) {
            $table->dropForeign(['mitra_master_id']);
            $table->dropColumn('mitra_master_id');
        });
    }
};
