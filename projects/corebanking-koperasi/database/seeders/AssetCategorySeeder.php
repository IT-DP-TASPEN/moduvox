<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Coa;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    private function coaId(string $code): ?int
    {
        return Coa::query()->where('coa_code', $code)->value('id');
    }

    private function normalizeName(string $name): string
    {
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default kas/bank: prefer 1-110, fallback ke COA ASSET leaf pertama
        $coaKas = $this->coaId('1-110')
            ?? Coa::query()->where('type', 'ASSET')->where('is_leaf', true)->where('is_active', true)->value('id');

        // 1. Parent Categories (berbasis kelompok masa manfaat)
        $parents = [
            'G1' => [
                'name' => 'Golongan I',
                'code' => 'G1',
                'depreciation_method' => 'STRAIGHT_LINE',
                'depreciation_rate' => 25,
                'useful_life_years' => 4,
                'asset_coa_id' => '1-211',
                'accumulated_depreciation_coa_id' => '1-221',
                'depreciation_expense_coa_id' => '5-161',
            ],
            'G2' => [
                'name' => 'Golongan II',
                'code' => 'G2',
                'depreciation_method' => 'STRAIGHT_LINE',
                'depreciation_rate' => 12.5,
                'useful_life_years' => 8,
                'asset_coa_id' => '1-212',
                'accumulated_depreciation_coa_id' => '1-222',
                'depreciation_expense_coa_id' => '5-162',
            ],
            'G3' => [
                'name' => 'Golongan III',
                'code' => 'G3',
                'depreciation_method' => 'STRAIGHT_LINE',
                'depreciation_rate' => 6.25,
                'useful_life_years' => 16,
                'asset_coa_id' => '1-213',
                'accumulated_depreciation_coa_id' => '1-223',
                'depreciation_expense_coa_id' => '5-163',
            ],
            'G4' => [
                'name' => 'Golongan IV',
                'code' => 'G4',
                'depreciation_method' => 'STRAIGHT_LINE',
                'depreciation_rate' => 5,
                'useful_life_years' => 20,
                'asset_coa_id' => '1-214',
                'accumulated_depreciation_coa_id' => '1-224',
                'depreciation_expense_coa_id' => '5-164',
            ],
            'BP' => [
                'name' => 'Bangunan Permanen',
                'code' => 'BP',
                'depreciation_method' => 'STRAIGHT_LINE',
                'depreciation_rate' => 5,
                'useful_life_years' => 20,
                'asset_coa_id' => '1-215',
                'accumulated_depreciation_coa_id' => '1-225',
                'depreciation_expense_coa_id' => '5-165',
            ],
            'BT' => [
                'name' => 'Bangunan Tidak Permanen',
                'code' => 'BT',
                'depreciation_method' => 'STRAIGHT_LINE',
                'depreciation_rate' => 10,
                'useful_life_years' => 10,
                'asset_coa_id' => '1-216',
                'accumulated_depreciation_coa_id' => '1-226',
                'depreciation_expense_coa_id' => '5-166',
            ],
        ];

        $createdParents = [];
        foreach ($parents as $key => $data) {
            $createdParents[$key] = AssetCategory::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $this->normalizeName($data['name']),
                    'parent_id' => null,
                    'depreciation_method' => $data['depreciation_method'],
                    'depreciation_rate' => $data['depreciation_rate'],
                    'useful_life_years' => $data['useful_life_years'],
                    'asset_coa_id' => $this->coaId($data['asset_coa_id']),
                    'accumulated_depreciation_coa_id' => $this->coaId($data['accumulated_depreciation_coa_id']),
                    'depreciation_expense_coa_id' => $this->coaId($data['depreciation_expense_coa_id']),
                    'is_active' => true,
                    'created_by' => 1,
                ]
            );
        }

        // 2. Sub-categories (Specific categories)
        $subCategories = [
            ['name' => 'Komputer & Printer', 'parent' => 'G1', 'code' => 'G1-01'],
            ['name' => 'Sepeda Motor', 'parent' => 'G1', 'code' => 'G1-02'],
            ['name' => 'Furnitur Kayu', 'parent' => 'G1', 'code' => 'G1-03'],
            ['name' => 'Perangkat Jaringan', 'parent' => 'G1', 'code' => 'G1-04'],
            
            ['name' => 'Mobil & Kendaraan', 'parent' => 'G2', 'code' => 'G2-01'],
            ['name' => 'Alat Komunikasi', 'parent' => 'G2', 'code' => 'G2-02'],
            ['name' => 'Furnitur Logam', 'parent' => 'G2', 'code' => 'G2-03'],
            ['name' => 'Pendingin Ruangan (AC)', 'parent' => 'G2', 'code' => 'G2-04'],
            ['name' => 'Peralatan Kantor Elektronik', 'parent' => 'G2', 'code' => 'G2-05'],

            ['name' => 'Mesin Operasional Berat', 'parent' => 'G3', 'code' => 'G3-01'],
            ['name' => 'Instalasi Teknis', 'parent' => 'G3', 'code' => 'G3-02'],

            ['name' => 'Infrastruktur Halaman', 'parent' => 'G4', 'code' => 'G4-01'],

            ['name' => 'Gedung Kantor', 'parent' => 'BP', 'code' => 'BP-01'],
            ['name' => 'Gudang', 'parent' => 'BP', 'code' => 'BP-02'],
            ['name' => 'Ruko Operasional', 'parent' => 'BP', 'code' => 'BP-03'],

            ['name' => 'Bangunan Semi Permanen', 'parent' => 'BT', 'code' => 'BT-01'],
            ['name' => 'Bangunan Sementara', 'parent' => 'BT', 'code' => 'BT-02'],
        ];

        foreach ($subCategories as $sub) {
            $parent = $createdParents[$sub['parent']];
            $category = AssetCategory::firstOrNew([
                'code' => $sub['code'],
            ]);

            $category->name = $this->normalizeName($sub['name']);
            $category->parent_id = $parent->id;
            $category->is_active = true;
            $category->created_by = 1;

            // Sub-kategori default inherit dari parent, tapi tetap hormati override yang sudah ada
            $category->depreciation_method = $category->depreciation_method ?: null;
            $category->depreciation_rate = $category->depreciation_rate ?: null;
            $category->useful_life_years = $category->useful_life_years ?: null;
            $category->asset_coa_id = $category->asset_coa_id ?: null;
            $category->accumulated_depreciation_coa_id = $category->accumulated_depreciation_coa_id ?: null;
            $category->depreciation_expense_coa_id = $category->depreciation_expense_coa_id ?: null;
            $category->save();
        }

        $this->command?->info('AssetCategorySeeder: kategori aset berhasil disempurnakan.');
    }
}
