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
        Schema::table('inv_mutasi', function (Blueprint $table) {
            $table->string('jenis_mutasi', 50)->nullable()->after('inventaris_id');
            $table->string('status', 50)->nullable()->after('keterangan');
            $table->unsignedBigInteger('user_id')->nullable()->after('status');
            $table->unsignedBigInteger('approval_user_id')->nullable()->after('user_id');

            // Change kantor_asal_id to be nullable
            $table->dropForeign(['kantor_asal_id']);
            $table->foreignId('kantor_asal_id')->nullable()->change()->constrained('mst_kantor')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_mutasi', function (Blueprint $table) {
            $table->dropForeign(['kantor_asal_id']);
            $table->foreignId('kantor_asal_id')->nullable(false)->change()->constrained('mst_kantor')->cascadeOnUpdate()->restrictOnDelete();

            $table->dropColumn(['jenis_mutasi', 'status', 'user_id', 'approval_user_id']);
        });
    }
};
