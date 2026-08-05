<?php

namespace Database\Seeders;

use App\Models\MstKantor;
use App\Models\MstGolongan;
use App\Models\MstJenis;
use App\Models\MstLokasi;
use App\Models\MstRuangan;
use App\Models\MstSumberDana;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Master Kantor Cabang
        |--------------------------------------------------------------------------
        */
        $kantorData = [
            ['kode' => '00', 'nama' => 'Kantor Pusat'],
            ['kode' => '01', 'nama' => 'KP Operasional'],
            ['kode' => '02', 'nama' => 'Cabang Bogor'],
            ['kode' => '03', 'nama' => 'Cabang Depok'],
            ['kode' => '04', 'nama' => 'Cabang Tangerang'],
            ['kode' => '05', 'nama' => 'Cabang Bekasi'],
            ['kode' => '06', 'nama' => 'Cabang Cirebon'],
            ['kode' => '07', 'nama' => 'Cabang Bandung'],
        ];

        foreach ($kantorData as $data) {
            MstKantor::firstOrCreate(['kode' => $data['kode']], $data);
        }

        /*
        |--------------------------------------------------------------------------
        | Master Golongan Aset
        |--------------------------------------------------------------------------
        */
        $golonganData = [
            ['kode' => '01', 'nama' => 'Tanah', 'umur_standar' => 0],
            ['kode' => '02', 'nama' => 'Golongan I (4 Tahun)', 'umur_standar' => 48],
            ['kode' => '03', 'nama' => 'Golongan II (8 Tahun)', 'umur_standar' => 96],
            ['kode' => '04', 'nama' => 'Golongan III (16 Tahun)', 'umur_standar' => 192],
            ['kode' => '05', 'nama' => 'Golongan IV (20 Tahun)', 'umur_standar' => 240],
            ['kode' => '06', 'nama' => 'Bangunan Permanen', 'umur_standar' => 240],
            ['kode' => '07', 'nama' => 'Bangunan Non-Permanen', 'umur_standar' => 120],
        ];

        foreach ($golonganData as $data) {
            MstGolongan::firstOrCreate(['kode' => $data['kode']], $data);
        }

        /*
        |--------------------------------------------------------------------------
        | Master Jenis Aset
        |--------------------------------------------------------------------------
        */
        $jenisData = [
            ['kode' => '01', 'nama' => 'Tanah'],
            ['kode' => '02', 'nama' => 'Bangunan'],
            ['kode' => '03', 'nama' => 'Kendaraan'],
            ['kode' => '04', 'nama' => 'Peralatan Kantor'],
            ['kode' => '05', 'nama' => 'Inventaris Kantor'],
            ['kode' => '06', 'nama' => 'Perangkat IT'],
            ['kode' => '07', 'nama' => 'Mesin & Peralatan'],
        ];

        foreach ($jenisData as $data) {
            MstJenis::firstOrCreate(['kode' => $data['kode']], $data);
        }

        /*
        |--------------------------------------------------------------------------
        | Master Lokasi
        |--------------------------------------------------------------------------
        */
        $lokasiData = [
            ['kode' => '01', 'nama' => 'Lantai 1'],
            ['kode' => '02', 'nama' => 'Lantai 2'],
            ['kode' => '03', 'nama' => 'Lantai 3'],
            ['kode' => '04', 'nama' => 'Basement'],
            ['kode' => '05', 'nama' => 'Gudang'],
        ];

        foreach ($lokasiData as $data) {
            MstLokasi::firstOrCreate(['kode' => $data['kode']], $data);
        }

        /*
        |--------------------------------------------------------------------------
        | Master Ruangan
        |--------------------------------------------------------------------------
        */
        $kpOperasional = MstKantor::where('kode', '01')->first();
        if ($kpOperasional) {
            $ruanganData = [
                ['kode' => '01', 'nama' => 'Ruang Direksi', 'kantor_id' => $kpOperasional->id],
                ['kode' => '02', 'nama' => 'Ruang Rapat', 'kantor_id' => $kpOperasional->id],
                ['kode' => '03', 'nama' => 'Ruang Operasional', 'kantor_id' => $kpOperasional->id],
                ['kode' => '04', 'nama' => 'Ruang Server', 'kantor_id' => $kpOperasional->id],
                ['kode' => '05', 'nama' => 'Lobi', 'kantor_id' => $kpOperasional->id],
            ];

            foreach ($ruanganData as $data) {
                MstRuangan::firstOrCreate(['kode' => $data['kode']], $data);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Master Sumber Dana
        |--------------------------------------------------------------------------
        */
        $sumberDanaData = [
            ['kode' => '01', 'nama' => 'Modal Disetor'],
            ['kode' => '02', 'nama' => 'Sewa Biaya'],
            ['kode' => '03', 'nama' => 'Laba Ditahan'],
            ['kode' => '04', 'nama' => 'Dana Bantuan'],
            ['kode' => '05', 'nama' => 'Hibah'],
        ];

        foreach ($sumberDanaData as $data) {
            MstSumberDana::firstOrCreate(['kode' => $data['kode']], $data);
        }
    }
}
