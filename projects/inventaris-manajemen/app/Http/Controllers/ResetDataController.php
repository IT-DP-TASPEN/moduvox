<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Inventaris;
use App\Models\InvMotor;
use App\Models\InvTanah;
use App\Models\InvMutasi;
use App\Models\InvImprovement;
use App\Models\PenyusutanBatch;
use App\Models\PenyusutanDetail;
use App\Models\ApiJournal;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResetDataController extends Controller
{
    /**
     * Tampilkan halaman Reset Data dengan info jumlah data.
     */
    public function index()
    {
        // Pastikan hanya Super Admin
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $counts = [
            'inventaris'        => Inventaris::withTrashed()->count(),
            'penyusutan_batch'  => PenyusutanBatch::count(),
            'penyusutan_detail' => PenyusutanDetail::count(),
            'inv_mutasi'        => InvMutasi::count(),
            'inv_improvement'   => InvImprovement::count(),
            'inv_motors'        => InvMotor::count(),
            'inv_tanahs'        => InvTanah::count(),
            'api_journals'      => ApiJournal::count(),
            'api_logs'          => ApiLog::count(),
        ];

        return view('system.reset', compact('counts'));
    }

    /**
     * Reset seluruh data inventaris dan data transaksi terkait.
     * Setelah reset, database kembali ke kondisi seperti baru install untuk modul inventaris.
     */
    public function resetInventaris(Request $request)
    {
        // Pastikan hanya Super Admin
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Validasi konfirmasi teks
        $request->validate([
            'confirmation' => 'required|in:RESET INVENTARIS',
        ], [
            'confirmation.in' => 'Teks konfirmasi tidak sesuai. Ketik "RESET INVENTARIS" untuk melanjutkan.',
        ]);

        try {
            DB::beginTransaction();

            // Urutan delete: child tables dulu, lalu parent
            // 1. API Logs (child of API Journals)
            $deletedApiLogs = DB::table('api_logs')->count();
            DB::table('api_logs')->delete();

            // 2. API Journals (child of Penyusutan Batch)
            $deletedApiJournals = DB::table('api_journals')->count();
            DB::table('api_journals')->delete();

            // 3. Penyusutan Detail (child of Penyusutan Batch & Inventaris)
            $deletedPenyusutanDetail = DB::table('penyusutan_detail')->count();
            DB::table('penyusutan_detail')->delete();

            // 4. Penyusutan Batch
            $deletedPenyusutanBatch = DB::table('penyusutan_batch')->count();
            DB::table('penyusutan_batch')->delete();

            // 5. Inv Mutasi (child of Inventaris)
            $deletedMutasi = DB::table('inv_mutasi')->count();
            DB::table('inv_mutasi')->delete();

            // 6. Inv Improvement (child of Inventaris)
            $deletedImprovement = DB::table('inv_improvement')->count();
            DB::table('inv_improvement')->delete();

            // 7. Inv Motors (child of Inventaris)
            $deletedMotors = DB::table('inv_motors')->count();
            DB::table('inv_motors')->delete();

            // 8. Inv Tanahs (child of Inventaris)
            $deletedTanahs = DB::table('inv_tanahs')->count();
            DB::table('inv_tanahs')->delete();

            // 9. Inventaris (parent - force delete termasuk soft-deleted)
            $deletedInventaris = DB::table('inventaris')->count();
            DB::table('inventaris')->delete();

            // Catat ke Audit Trail
            AuditTrail::create([
                'table_name'  => 'inventaris',
                'record_id'   => 0,
                'action'      => 'RESET_ALL',
                'old_values'  => [
                    'inventaris'        => $deletedInventaris,
                    'penyusutan_batch'  => $deletedPenyusutanBatch,
                    'penyusutan_detail' => $deletedPenyusutanDetail,
                    'inv_mutasi'        => $deletedMutasi,
                    'inv_improvement'   => $deletedImprovement,
                    'inv_motors'        => $deletedMotors,
                    'inv_tanahs'        => $deletedTanahs,
                    'api_journals'      => $deletedApiJournals,
                    'api_logs'          => $deletedApiLogs,
                ],
                'new_values'  => ['status' => 'ALL DATA RESET'],
                'user_id'     => auth()->id(),
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            DB::commit();

            $totalDeleted = $deletedInventaris + $deletedPenyusutanBatch + $deletedPenyusutanDetail
                          + $deletedMutasi + $deletedImprovement + $deletedMotors + $deletedTanahs
                          + $deletedApiJournals + $deletedApiLogs;

            return redirect()->route('system.reset.index')
                ->with('success', "Reset berhasil! Total {$totalDeleted} record dihapus dari 9 tabel. Database modul inventaris kembali ke kondisi awal.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat reset data: ' . $e->getMessage());
        }
    }
}
