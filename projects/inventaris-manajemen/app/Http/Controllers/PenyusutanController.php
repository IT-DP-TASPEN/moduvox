<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Enums\DepreciationBatchStatus;
use App\Models\Inventaris;
use App\Models\PenyusutanBatch;
use App\Models\PenyusutanDetail;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PenyusutanController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $batches = PenyusutanBatch::with('approver')->latest('periode_ym')->get();
        return view('penyusutan.index', compact('batches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan' => 'required|numeric|min:1|max:12',
            'tahun' => 'required|numeric|min:2020|max:2100',
        ]);

        $bulan = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
        $periode_ym = $request->tahun . $bulan;

        // Cek apakah sudah pernah digenerate
        if (PenyusutanBatch::where('periode_ym', $periode_ym)->exists()) {
            return redirect()->back()->with('error', 'Penyusutan untuk periode ini sudah pernah diproses.');
        }

        try {
            DB::beginTransaction();

            // 1. Buat Header Batch
            $batch = PenyusutanBatch::create([
                'periode_ym' => $periode_ym,
                'status' => DepreciationBatchStatus::DRAFT->value,
                'catatan' => "Generate otomatis tanggal " . now()->format('d/m/Y'),
            ]);

            // 2. Tarik semua aset yang memenuhi syarat disusutkan
            //    Syarat: Bukan Tanah, Status Aktif, Nilai Buku > 1, Umur Ekonomis > 0
            $assets = Inventaris::with('golongan')
                        ->where('status', AssetStatus::AKTIF->value)
                        ->where('nilai_buku', '>', 1)
                        ->where('umur_bulan', '>', 0)
                        // Ignore Tanah (Tanah is handled by isTanah() helper in model, but we can filter by golongan if we know the code.
                        // For safety, we fetch all and filter in collection, or assume Golongan with umur_standar > 0 is depreciable.
                        ->whereHas('golongan', function($q) {
                            $q->where('umur_standar', '>', 0);
                        })
                        ->get();

            $totalBeban = 0;
            $totalAset = 0;

            foreach ($assets as $asset) {
                // Pastikan umur_bulan tidak nol untuk menghindari division by zero
                if ($asset->umur_bulan <= 0) continue;

                $beban = round($asset->harga_perolehan / $asset->umur_bulan, 2);

                // Aturan Sisa Rp 1,00
                if (($asset->nilai_buku - $beban) <= 1) {
                    $beban = $asset->nilai_buku - 1; // Sisakan 1
                }

                if ($beban <= 0) continue;

                $nilai_buku_sebelum = $asset->nilai_buku;
                $nilai_buku_sesudah = $asset->nilai_buku - $beban;
                $akumulasi_sekarang = $asset->akumulasi_penyusutan + $beban;

                // Insert Detail Penyusutan
                PenyusutanDetail::create([
                    'batch_id' => $batch->id,
                    'inventaris_id' => $asset->id,
                    'kantor_id' => $asset->kantor_id,
                    'beban_bulan_ini' => $beban,
                    'nilai_buku_sebelum' => $nilai_buku_sebelum,
                    'nilai_buku_sesudah' => $nilai_buku_sesudah,
                    'akumulasi' => $akumulasi_sekarang,
                ]);

                // Update Master Aset
                $asset->update([
                    'nilai_buku' => $nilai_buku_sesudah,
                    'akumulasi_penyusutan' => $akumulasi_sekarang,
                ]);

                $totalBeban += $beban;
                $totalAset++;
            }

            DB::commit();

            return redirect()->route('penyusutan.index')
                ->with('success', "Proses penyusutan selesai. {$totalAset} aset diproses dengan total beban " . \App\Helpers\FormatHelper::rupiah($totalBeban));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $batch = PenyusutanBatch::with(['details.inventaris.kantor', 'details.inventaris.golongan', 'approver'])->findOrFail($id);
        
        // Group summary for journals mapping
        $summary = DB::table('penyusutan_detail')
                    ->join('inventaris', 'penyusutan_detail.inventaris_id', '=', 'inventaris.id')
                    ->join('mst_golongan', 'inventaris.golongan_id', '=', 'mst_golongan.id')
                    ->join('mst_kantor', 'penyusutan_detail.kantor_id', '=', 'mst_kantor.id')
                    ->where('penyusutan_detail.batch_id', $id)
                    ->select('penyusutan_detail.kantor_id', 'mst_kantor.kode as kode_kantor', 'mst_kantor.nama as nama_kantor', 'inventaris.golongan_id', 'mst_golongan.nama as nama_golongan', DB::raw('SUM(beban_bulan_ini) as total_beban'))
                    ->groupBy('penyusutan_detail.kantor_id', 'mst_kantor.kode', 'mst_kantor.nama', 'inventaris.golongan_id', 'mst_golongan.nama')
                    ->orderBy('mst_kantor.kode')
                    ->orderBy('mst_golongan.nama')
                    ->get();

        return view('penyusutan.show', compact('batch', 'summary'));
    }

    public function approve(string $id)
    {
        $batch = PenyusutanBatch::findOrFail($id);

        if (!$batch->isDraft()) {
            return redirect()->back()->with('error', 'Batch ini tidak dalam status Draft.');
        }

        try {
            DB::beginTransaction();

            $batch->update([
                'status' => DepreciationBatchStatus::CLOSED->value,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Generate & Send API Journals to FinCloud
            \App\Services\FinCloudApiService::generateJournals($batch);
            
            // We can dispatch a job or process synchronously. We'll do it synchronously for now
            // or push it to background task if queue is configured.
            \App\Services\FinCloudApiService::processJournals($batch);

            DB::commit();

            return redirect()->back()->with('success', 'Batch penyusutan berhasil disetujui. Jurnal telah digenerate dan dikirim ke FinCloud.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat approve: ' . $e->getMessage());
        }
    }
}
