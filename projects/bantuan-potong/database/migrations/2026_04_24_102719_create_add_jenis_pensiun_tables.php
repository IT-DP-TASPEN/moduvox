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
        Schema::table('permintaan_checkings', function (Blueprint $table) {
            $table->text('jenis_pensiun_added')->nullable()->after('wilayah');
        });

        Schema::table('permintaan_checking_internals', function (Blueprint $table) {
            $table->text('jenis_pensiun_added')->nullable()->after('wilayah');
        });

        Schema::table('permintaan_estimasis', function (Blueprint $table) {
            $table->text('jenis_pensiun_added')->nullable()->after('wilayah');
        });

        Schema::table('permintaan_estimasi_internals', function (Blueprint $table) {
            $table->text('jenis_pensiun_added')->nullable()->after('wilayah');
        });

        Schema::table('permintaan_open_flagging_tifs', function (Blueprint $table) {
            $table->text('jenis_pensiun_added')->nullable()->after('wilayah');
        });

        Schema::table('permintaan_open_flagging_internals', function (Blueprint $table) {
            $table->text('jenis_pensiun_added')->nullable()->after('wilayah');
        });

        Schema::table('permintaan_flagging_mutasi_tifs', function (Blueprint $table) {
            $table->text('jenis_pensiun_added')->nullable()->after('wilayah');
        });

        Schema::table('permintaan_flagging_mutasi_tif_internals', function (Blueprint $table) {
            $table->text('jenis_pensiun_added')->nullable()->after('wilayah');
        });

        Schema::table('permintaan_flagging_tifs', function (Blueprint $table) {
            $table->text('jenis_pensiun_added')->nullable()->after('wilayah');
        });

        Schema::table('permintaan_flagging_tif_internals', function (Blueprint $table) {
            $table->text('jenis_pensiun_added')->nullable()->after('wilayah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permintaan_checkings', function (Blueprint $table) {
            $table->dropColumn('jenis_pensiun_added');
        });

        Schema::table('permintaan_checking_internals', function (Blueprint $table) {
            $table->dropColumn('jenis_pensiun_added');
        });

        Schema::table('permintaan_estimasis', function (Blueprint $table) {
            $table->dropColumn('jenis_pensiun_added');
        });

        Schema::table('permintaan_estimasi_internals', function (Blueprint $table) {
            $table->dropColumn('jenis_pensiun_added');
        });

        Schema::table('permintaan_open_flagging_tifs', function (Blueprint $table) {
            $table->dropColumn('jenis_pensiun_added');
        });

        Schema::table('permintaan_open_flagging_internals', function (Blueprint $table) {
            $table->dropColumn('jenis_pensiun_added');
        });

        Schema::table('permintaan_flagging_mutasi_tifs', function (Blueprint $table) {
            $table->dropColumn('jenis_pensiun_added');
        });

        Schema::table('permintaan_flagging_mutasi_tif_internals', function (Blueprint $table) {
            $table->dropColumn('jenis_pensiun_added');
        });

        Schema::table('permintaan_flagging_tifs', function (Blueprint $table) {
            $table->dropColumn('jenis_pensiun_added');
        });

        Schema::table('permintaan_flagging_tif_internals', function (Blueprint $table) {
            $table->dropColumn('jenis_pensiun_added');
        });
    }
};
