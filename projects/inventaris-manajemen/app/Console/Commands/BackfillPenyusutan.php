<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventaris;
use App\Models\PenyusutanDetail;
use Illuminate\Support\Facades\DB;

class BackfillPenyusutan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'penyusutan:backfill {--rekening= : Spesifik rekening jika hanya ingin 1 aset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengkalkulasi ulang Nilai Buku Sebelum dan Sesudah untuk histori penyusutan yang bernilai 0 (hasil import legacy)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai kalkulasi backfill histori penyusutan...');
        
        try {
            DB::beginTransaction();

            $query = Inventaris::whereHas('penyusutanDetail', function($q) {
                $q->where('nilai_buku_sebelum', 0);
            });

            if ($this->option('rekening')) {
                $query->where('rekening', $this->option('rekening'));
            }

            $assetsWith0 = $query->get();
            $this->info("Ditemukan " . $assetsWith0->count() . " aset yang butuh di-backfill.");

            $processedCount = 0;
            $bar = $this->output->createProgressBar($assetsWith0->count());

            foreach ($assetsWith0 as $asset) {
                // Get all depreciation details for this asset, chronologically
                $details = PenyusutanDetail::join('penyusutan_batch', 'penyusutan_detail.batch_id', '=', 'penyusutan_batch.id')
                    ->where('penyusutan_detail.inventaris_id', $asset->id)
                    ->orderBy('penyusutan_batch.periode_ym', 'asc')
                    ->select('penyusutan_detail.*')
                    ->get();
                    
                $nilaiBuku = $asset->harga_perolehan;
                $akumulasi = 0;
                
                foreach ($details as $detail) {
                    $beban = floatval($detail->beban_bulan_ini);
                    
                    $detail->nilai_buku_sebelum = $nilaiBuku;
                    $detail->nilai_buku_sesudah = $nilaiBuku - $beban;
                    
                    $akumulasi += $beban;
                    $detail->akumulasi = $akumulasi;
                    
                    $detail->save();
                    
                    $nilaiBuku = $detail->nilai_buku_sesudah;
                    $processedCount++;
                }
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);

            DB::commit();
            $this->info("Berhasil memproses {$processedCount} record histori penyusutan!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan: " . $e->getMessage());
        }
    }
}
