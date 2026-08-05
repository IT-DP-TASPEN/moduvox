<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventaris;
use App\Models\MstKantor;
use App\Models\MstGolongan;
use App\Models\MstJenis;
use App\Models\MstLokasi;
use App\Models\MstRuangan;
use App\Models\MstSumberDana;
use App\Models\InvMutasi;
use App\Models\PenyusutanBatch;
use App\Models\PenyusutanDetail;
use Faker\Factory as Faker;
use Carbon\Carbon;
use App\Enums\AssetStatus;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $kantors = MstKantor::all();
        $golongans = MstGolongan::all();
        $jenises = MstJenis::all();
        $lokasis = MstLokasi::all();
        $sumberDanas = MstSumberDana::all();
        $ruangans = MstRuangan::all();

        // Create 200 realistic assets
        for ($i = 0; $i < 200; $i++) {
            $kantor = $kantors->random();
            $golongan = $golongans->random();
            $jenis = $jenises->random();
            $lokasi = $lokasis->random();
            $sumberDana = $sumberDanas->random();
            $ruangan = $ruangans->where('kantor_id', $kantor->id)->first() ?? $ruangans->random();

            $hargaPerolehan = $faker->randomElement([5000000, 15000000, 25000000, 100000000, 500000000, 1500000]);
            $tglPerolehan = Carbon::now()->subMonths(rand(1, 48));
            
            $umurBulan = $tglPerolehan->diffInMonths(Carbon::now());
            
            $akumulasiPenyusutan = 0;
            if ($golongan->umur_standar > 0) {
                $penyusutanBulan = $hargaPerolehan / $golongan->umur_standar;
                $akumulasiPenyusutan = min($hargaPerolehan, $penyusutanBulan * $umurBulan);
            }
            
            $nilaiBuku = $hargaPerolehan - $akumulasiPenyusutan;

            $namaAset = $this->generateNamaAset($jenis->nama, $faker);

            $data = [
                'kantor_id' => $kantor->id,
                'golongan_id' => $golongan->id,
                'jenis_id' => $jenis->id,
                'ruangan_id' => $ruangan->id,
                'lokasi_id' => $lokasi->id,
                'sumber_id' => $sumberDana->id,
                'nama_aset' => $namaAset,
                'merk' => $faker->company,
                'no_seri' => strtoupper($faker->bothify('SN-####-????')),
                'harga_perolehan' => $hargaPerolehan,
                'nilai_buku' => $nilaiBuku,
                'akumulasi_penyusutan' => $akumulasiPenyusutan,
                'tgl_perolehan' => $tglPerolehan->format('Y-m-d'),
                'umur_bulan' => $umurBulan,
                'keterangan' => 'Demo data ' . $faker->sentence(3),
                'status' => AssetStatus::AKTIF->value,
            ];

            $data['rekening'] = Inventaris::generateNomorInventaris($data);

            $inventaris = Inventaris::create($data);

            // Mutation log
            InvMutasi::create([
                'inventaris_id' => $inventaris->id,
                'jenis_mutasi' => 'BARU',
                'tgl_mutasi' => $tglPerolehan,
                'kantor_asal_id' => null,
                'kantor_tujuan_id' => $kantor->id,
                'keterangan' => 'Pencatatan aset baru',
                'status' => 'APPROVED',
                'user_id' => 1
            ]);

            // If it's vehicle
            if (str_contains(strtolower($jenis->nama), 'kendaraan')) {
                $inventaris->motor()->create([
                    'atas_nama' => 'PT Moduvox Tech ID',
                    'tahun_pembuatan' => $tglPerolehan->format('Y'),
                    'tahun_rakit' => $tglPerolehan->format('Y'),
                    'warna' => $faker->colorName,
                    'no_rangka' => strtoupper($faker->bothify('MH1??????#?######')),
                    'no_mesin' => strtoupper($faker->bothify('J????E-#######')),
                    'no_bpkb' => strtoupper($faker->bothify('M#######')),
                    'no_polisi' => strtoupper($faker->bothify('B #### ???')),
                    'tgl_pajak' => Carbon::parse($tglPerolehan)->addYears(rand(1,5))->format('Y-m-d'),
                ]);
            }
        }
        
        // Generate Penyusutan Batch for the last 48 months
        $batches = [];
        for ($m = 48; $m >= 1; $m--) {
            $period = Carbon::now()->subMonths($m);
            $ym = $period->format('Ym');
            $batch = PenyusutanBatch::create([
                'periode_ym' => $ym,
                'status' => 'APPROVED',
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => $period->copy()->endOfMonth(),
                'catatan' => 'Penyusutan reguler'
            ]);
            $batches[$ym] = $batch;
        }

        $allAssets = Inventaris::with('golongan')->get();
        foreach ($allAssets as $asset) {
            $golongan = $asset->golongan;
            if (!$golongan || $golongan->umur_standar <= 0) continue;

            $hargaPerolehan = $asset->harga_perolehan;
            $penyusutanBulan = $hargaPerolehan / $golongan->umur_standar;
            
            $tglPerolehan = Carbon::parse($asset->tgl_perolehan);
            $startPeriod = $tglPerolehan->copy()->addMonth()->startOfMonth();
            $endPeriod = Carbon::now()->startOfMonth();

            $currentBuku = $hargaPerolehan;
            $currentAkumulasi = 0;
            
            $iterDate = $startPeriod->copy();
            $umurKe = 1;
            
            // Limit to actual max umur_standar
            while ($iterDate->lessThan($endPeriod) && $umurKe <= $golongan->umur_standar) {
                $ym = $iterDate->format('Ym');
                if (isset($batches[$ym])) {
                    $nilaiBukuSebelum = $currentBuku;
                    $bebanBulanIni = $penyusutanBulan;
                    
                    $currentAkumulasi += $bebanBulanIni;
                    $currentBuku -= $bebanBulanIni;
                    
                    if ($currentBuku < 0) {
                        $bebanBulanIni = $nilaiBukuSebelum;
                        $currentBuku = 0;
                        $currentAkumulasi = $hargaPerolehan;
                    }

                    PenyusutanDetail::create([
                        'batch_id' => $batches[$ym]->id,
                        'inventaris_id' => $asset->id,
                        'kantor_id' => $asset->kantor_id,
                        'beban_bulan_ini' => $bebanBulanIni,
                        'nilai_buku_sebelum' => $nilaiBukuSebelum,
                        'nilai_buku_sesudah' => $currentBuku,
                        'akumulasi' => $currentAkumulasi
                    ]);
                }
                $iterDate->addMonth();
                $umurKe++;
            }
        }
    }

    private function generateNamaAset($jenisNama, $faker)
    {
        $jenisNama = strtolower($jenisNama);
        if (str_contains($jenisNama, 'kendaraan')) {
            return $faker->randomElement(['Toyota Avanza', 'Honda Beat', 'Daihatsu Xenia', 'Mitsubishi Xpander', 'Honda Vario', 'Yamaha NMAX']);
        } elseif (str_contains($jenisNama, 'perangkat it')) {
            return $faker->randomElement(['Laptop Lenovo Thinkpad', 'PC Dell Optiplex', 'Printer Epson L3110', 'MacBook Pro M1', 'Server HP ProLiant', 'Router Mikrotik']);
        } elseif (str_contains($jenisNama, 'peralatan kantor') || str_contains($jenisNama, 'inventaris kantor')) {
            return $faker->randomElement(['Meja Kerja Direktur', 'Kursi Ergonomis', 'Lemari Arsip Besi', 'Whiteboard', 'AC Daikin 1 PK', 'Brankas Krisbow']);
        } elseif (str_contains($jenisNama, 'bangunan')) {
            return $faker->randomElement(['Gedung Kantor Pusat', 'Ruko Cabang', 'Gudang Penyimpanan', 'Mess Karyawan']);
        } elseif (str_contains($jenisNama, 'tanah')) {
            return $faker->randomElement(['Tanah Kosong Depok', 'Lahan Parkir', 'Tanah Kavling']);
        }
        return 'Aset ' . $faker->word;
    }
}
