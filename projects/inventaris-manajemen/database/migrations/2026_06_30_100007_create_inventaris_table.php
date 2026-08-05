<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaris', function (Blueprint $table) {
            $table->id();
            $table->string('rekening', 50)->unique()->comment('Nomor Rekening Aset / Barcode');
            $table->string('nama_aset', 255);
            $table->foreignId('kantor_id')->constrained('mst_kantor')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('golongan_id')->constrained('mst_golongan')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('jenis_id')->constrained('mst_jenis')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('ruangan_id')->nullable()->constrained('mst_ruangan')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('lokasi_id')->nullable()->constrained('mst_lokasi')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('sumber_id')->nullable()->constrained('mst_sumber_dana')->cascadeOnUpdate()->nullOnDelete();
            $table->date('tgl_perolehan');
            $table->decimal('harga_perolehan', 15, 2)->default(0);
            $table->decimal('nilai_buku', 15, 2)->default(0);
            $table->decimal('akumulasi_penyusutan', 15, 2)->default(0);
            $table->integer('umur_bulan')->default(0)->comment('Masa manfaat ekonomis dalam bulan');
            $table->string('status', 20)->default('AKTIF');
            $table->string('merk', 100)->nullable();
            $table->string('no_seri', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('alasan_hapus', 255)->nullable()->comment('Alasan write-off');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->index('rekening');
            $table->index('kantor_id');
            $table->index('golongan_id');
            $table->index('status');
            $table->index('tgl_perolehan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaris');
    }
};
