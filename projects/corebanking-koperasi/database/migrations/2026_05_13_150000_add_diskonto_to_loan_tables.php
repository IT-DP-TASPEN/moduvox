<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── loan_products: tambah flag diskonto ──────────────────────────────
        Schema::table('loan_products', function (Blueprint $table) {
            $table->boolean('is_diskonto')->default(false)->after('is_active')
                ->comment('Produk diskonto: bunga dibayar di muka; hanya FLAT');
        });

        // ── loan_accounts: simpan flag diskonto + nominal diskonto di muka ───
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->boolean('is_diskonto')->default(false)->after('calculation_method')
                ->comment('Klon dari produk, digunakan untuk menentukan skema angsuran diskonto');
            $table->decimal('diskonto_upfront_amount', 20, 2)->default(0)->after('is_diskonto')
                ->comment('Nominal bunga dibayar di muka = bunga_bulanan × tenor');
        });
    }

    public function down(): void
    {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->dropColumn(['is_diskonto', 'diskonto_upfront_amount']);
        });

        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn('is_diskonto');
        });
    }
};
