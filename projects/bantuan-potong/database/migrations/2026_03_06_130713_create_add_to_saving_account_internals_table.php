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
        Schema::table('saving_account_internals', function (Blueprint $table) {
            $table->string('nip_notas');
            $table->string('rek_transfer');
            $table->string('bank_transfer');
            $table->text('tujuan_transfer');
            $table->string('doc_si');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saving_account_internals', function (Blueprint $table) {
            $table->dropColumn(['nip_notas', 'rek_transfer', 'bank_transfer', 'tujuan_transfer', 'doc_si']);
        });
    }
};
