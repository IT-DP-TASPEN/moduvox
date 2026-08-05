<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventaris;
use App\Models\PenyusutanBatch;
use App\Models\PenyusutanDetail;
use App\Enums\DepreciationBatchStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BackfillNovDecPenyusutan extends Command
{
    protected $signature = 'penyusutan:backfill-nov-dec';
    protected $description = 'Backfill histori penyusutan untuk November dan Desember 2025 tanpa merubah tabel Master Inventaris.';

    public function handle()
    {
        $this->info('Memulai backfill histori penyusutan Nov & Des 2025...');
        
        try {
            DB::beginTransaction();

            $nov_ym = '202511';
            $des_ym = '202512';
            $nov_date = Carbon::create(2025, 11, 30);
            $des_date = Carbon::create(2025, 12, 31);

            // Buat Batch jika belum ada
            $batchNov = PenyusutanBatch::firstOrCreate(
                ['periode_ym' => $nov_ym],
                ['status' => DepreciationBatchStatus::APPROVED->value, 'catatan' => 'Backfill Migrasi']
            );
            $batchDes = PenyusutanBatch::firstOrCreate(
                ['periode_ym' => $des_ym],
                ['status' => DepreciationBatchStatus::APPROVED->value, 'catatan' => 'Backfill Migrasi']
            );

            // Clear existing backfill detail jika script ini di-rerun
            PenyusutanDetail::whereIn('batch_id', [$batchNov->id, $batchDes->id])->delete();

            $assets = Inventaris::with(['golongan', 'penyusutanDetail'])->get();
            $bar = $this->output->createProgressBar($assets->count());

            $processedNov = 0;
            $processedDes = 0;

            foreach ($assets as $asset) {
                if (!$asset->golongan || $asset->golongan->kode == '01') {
                    $bar->advance();
                    continue;
                }

                // Hitung baseline Okt
                $akumulasi_okt = $asset->penyusutanDetail->sum('beban_bulan_ini');
                $buku_okt = $asset->harga_perolehan - $akumulasi_okt;
                
                $lastSusut = $asset->penyusutanDetail->last();
                $susut_bulanan = $lastSusut ? $lastSusut->beban_bulan_ini : 0;
                
                if ($susut_bulanan == 0 && $asset->golongan->umur_standar > 0) {
                    $susut_bulanan = $asset->harga_perolehan / $asset->golongan->umur_standar;
                }

                $current_akumulasi = $akumulasi_okt;
                $current_buku = $buku_okt;

                // Nov 2025
                if ($asset->tgl_perolehan <= $nov_date && $current_buku > 0) {
                    $beban = min($susut_bulanan, $current_buku);
                    if ($beban > 0) {
                        PenyusutanDetail::create([
                            'batch_id' => $batchNov->id,
                            'inventaris_id' => $asset->id,
                            'kantor_id' => $asset->kantor_id,
                            'beban_bulan_ini' => $beban,
                            'nilai_buku_sebelum' => $current_buku,
                            'nilai_buku_sesudah' => $current_buku - $beban,
                            'akumulasi' => $current_akumulasi + $beban,
                        ]);
                        $current_akumulasi += $beban;
                        $current_buku -= $beban;
                        $processedNov++;
                    }
                }

                // Des 2025
                if ($asset->tgl_perolehan <= $des_date && $current_buku > 0) {
                    $beban = min($susut_bulanan, $current_buku);
                    if ($beban > 0) {
                        PenyusutanDetail::create([
                            'batch_id' => $batchDes->id,
                            'inventaris_id' => $asset->id,
                            'kantor_id' => $asset->kantor_id,
                            'beban_bulan_ini' => $beban,
                            'nilai_buku_sebelum' => $current_buku,
                            'nilai_buku_sesudah' => $current_buku - $beban,
                            'akumulasi' => $current_akumulasi + $beban,
                        ]);
                        $processedDes++;
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            DB::commit();

            $this->info("Berhasil memproses $processedNov baris untuk Nov 2025.");
            $this->info("Berhasil memproses $processedDes baris untuk Des 2025.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
