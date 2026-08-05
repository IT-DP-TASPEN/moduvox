<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventaris;
use App\Models\PenyusutanDetail;
use App\Models\InvMutasi;
use Illuminate\Support\Facades\DB;

class FixLegacyAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventaris:fix-legacy {--kantor=1 : ID Kantor untuk assigned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Koreksi 5 Aset Legacy (prefix 00) dengan assign kantor_id yang benar dan regenerate rekening';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mencari aset legacy dengan prefix 00...');
        
        try {
            DB::beginTransaction();

            $legacyAssets = Inventaris::where('rekening', 'like', '00.%')->get();
            $kantorId = $this->option('kantor');

            if ($legacyAssets->isEmpty()) {
                $this->info("Tidak ada aset legacy (prefix 00) yang ditemukan.");
                return;
            }

            $this->info("Ditemukan " . $legacyAssets->count() . " aset legacy. Akan diubah ke kantor_id = {$kantorId} dan di-regenerate rekeningnya.");
            
            $bar = $this->output->createProgressBar($legacyAssets->count());

            foreach ($legacyAssets as $asset) {
                $oldRekening = $asset->rekening;
                
                // Update kantor_id
                $asset->kantor_id = $kantorId;
                
                // Generate new rekening based on new kantor_id
                $data = $asset->toArray();
                $newRekening = Inventaris::generateNomorInventaris($data);
                $asset->rekening = $newRekening;
                $asset->save();
                
                // Update related records
                PenyusutanDetail::where('inventaris_id', $asset->id)->update(['kantor_id' => $kantorId]);
                
                // Update Mutasi
                // Mutasi asal yang sebelumnya null tapi tujuan 2 (atau apapun) kita update jadi $kantorId
                InvMutasi::where('inventaris_id', $asset->id)
                    ->update(['kantor_tujuan_id' => $kantorId]);

                $this->line("\n[OK] {$oldRekening} -> {$newRekening}");
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);

            DB::commit();
            $this->info("Semua perubahan berhasil disimpan!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan: " . $e->getMessage());
        }
    }
}
