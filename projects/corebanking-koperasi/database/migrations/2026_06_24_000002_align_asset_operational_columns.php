<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('asset_categories', 'coa_aset_id')) {
                $table->foreignId('coa_aset_id')->nullable()->after('is_active')->constrained('coas')->nullOnDelete();
            }
            if (!Schema::hasColumn('asset_categories', 'coa_akum_penyusutan_id')) {
                $table->foreignId('coa_akum_penyusutan_id')->nullable()->after('coa_aset_id')->constrained('coas')->nullOnDelete();
            }
            if (!Schema::hasColumn('asset_categories', 'coa_beban_penyusutan_id')) {
                $table->foreignId('coa_beban_penyusutan_id')->nullable()->after('coa_akum_penyusutan_id')->constrained('coas')->nullOnDelete();
            }
            if (!Schema::hasColumn('asset_categories', 'coa_kas_id')) {
                $table->foreignId('coa_kas_id')->nullable()->after('coa_beban_penyusutan_id')->constrained('coas')->nullOnDelete();
            }
            if (!Schema::hasColumn('asset_categories', 'depreciation_rate_annual')) {
                $table->decimal('depreciation_rate_annual', 8, 4)->nullable()->after('depreciation_method');
            }
            if (!Schema::hasColumn('asset_categories', 'useful_life_months')) {
                $table->integer('useful_life_months')->nullable()->after('depreciation_rate_annual');
            }
        });

        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'salvage_value')) {
                $table->decimal('salvage_value', 20, 2)->default(0)->after('purchase_price');
            }
            if (!Schema::hasColumn('assets', 'useful_life_months')) {
                $table->integer('useful_life_months')->nullable()->after('salvage_value');
            }
            if (!Schema::hasColumn('assets', 'depreciation_nominal')) {
                $table->decimal('depreciation_nominal', 20, 2)->nullable()->after('depreciation_rate');
            }
            if (!Schema::hasColumn('assets', 'current_book_value')) {
                $table->decimal('current_book_value', 20, 2)->default(0)->after('accumulated_depreciation');
            }
            if (!Schema::hasColumn('assets', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('name');
            }
            if (!Schema::hasColumn('assets', 'vendor')) {
                $table->string('vendor')->nullable()->after('location');
            }
            if (!Schema::hasColumn('assets', 'condition')) {
                $table->string('condition')->default('GOOD')->after('vendor');
            }
            if (!Schema::hasColumn('assets', 'description')) {
                $table->text('description')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $columns = [
                'description',
                'condition',
                'vendor',
                'serial_number',
                'current_book_value',
                'depreciation_nominal',
                'useful_life_months',
                'salvage_value',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('assets', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('asset_categories', function (Blueprint $table) {
            foreach ([
                'useful_life_months',
                'depreciation_rate_annual',
                'coa_kas_id',
                'coa_beban_penyusutan_id',
                'coa_akum_penyusutan_id',
                'coa_aset_id',
                'description',
            ] as $column) {
                if (Schema::hasColumn('asset_categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
