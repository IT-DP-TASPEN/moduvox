<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marketing_targets', function (Blueprint $table) {
            $table->id();
            $table->string('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('jumlah_hari_kerja');
            $table->decimal('nominal_target', 15, 2);
            $table->timestamps();
        });

        Schema::table('marketing_masters', function (Blueprint $table) {
            $table->foreignId('marketing_target_id')
                ->nullable()
                ->after('branch_master_id')
                ->constrained('marketing_targets')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('marketing_masters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marketing_target_id');
        });

        Schema::dropIfExists('marketing_targets');
    }
};
