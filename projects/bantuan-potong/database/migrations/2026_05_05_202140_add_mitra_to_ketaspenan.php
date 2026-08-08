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


        Schema::table('permintaan_checking_internals', function (Blueprint $table) {
            $table->foreignId('mitra_master_id')->nullable()->after('jenis_pensiun_added')->constrained('mitra_masters')->cascadeOnDelete();
        });



        Schema::table('permintaan_estimasi_internals', function (Blueprint $table) {
            $table->foreignId('mitra_master_id')->nullable()->after('jenis_pensiun_added')->constrained('mitra_masters')->cascadeOnDelete();
        });



        Schema::table('permintaan_open_flagging_internals', function (Blueprint $table) {
            $table->foreignId('mitra_master_id')->nullable()->after('jenis_pensiun_added')->constrained('mitra_masters')->cascadeOnDelete();
        });



        Schema::table('permintaan_flagging_mutasi_tif_internals', function (Blueprint $table) {
            $table->foreignId('mitra_master_id')->nullable()->after('jenis_pensiun_added')->constrained('mitra_masters')->cascadeOnDelete();
        });


        Schema::table('permintaan_flagging_tif_internals', function (Blueprint $table) {
            $table->foreignId('mitra_master_id')->nullable()->after('jenis_pensiun_added')->constrained('mitra_masters')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permintaan_checking_internals', function (Blueprint $table) {
            $table->dropForeign(['mitra_master_id']);
            $table->dropColumn('mitra_master_id');
        });

        Schema::table('permintaan_estimasi_internals', function (Blueprint $table) {
            $table->dropForeign(['mitra_master_id']);
            $table->dropColumn('mitra_master_id');
        });

        Schema::table('permintaan_open_flagging_internals', function (Blueprint $table) {
            $table->dropForeign(['mitra_master_id']);
            $table->dropColumn('mitra_master_id');
        });

        Schema::table('permintaan_flagging_mutasi_tif_internals', function (Blueprint $table) {
            $table->dropForeign(['mitra_master_id']);
            $table->dropColumn('mitra_master_id');
        });

        Schema::table('permintaan_flagging_tif_internals', function (Blueprint $table) {
            $table->dropForeign(['mitra_master_id']);
            $table->dropColumn('mitra_master_id');
        });
    }
};
