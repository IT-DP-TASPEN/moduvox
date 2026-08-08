<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('mitra_master_id')
                ->nullable()
                ->after('email')
                ->constrained('mitra_masters')
                ->nullOnDelete();

            $table->foreignId('mitra_branch_id')
                ->nullable()
                ->after('mitra_master_id')
                ->constrained('mitra_branches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['mitra_master_id']);
            $table->dropForeign(['mitra_branch_id']);
            $table->dropColumn(['mitra_master_id', 'mitra_branch_id']);
        });
    }
};