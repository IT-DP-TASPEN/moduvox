<?php

namespace App\Http\Controllers;

use App\Enums\JournalState;
use App\Models\ApiJournal;
use App\Models\ApiLog;
use App\Models\MstKantor;
use App\Models\PenyusutanBatch;
use Illuminate\Http\Request;

class ApiJournalController extends Controller
{
    public function index(Request $request)
    {
        $query = ApiJournal::with('batch');

        // Filter by cabang (kode kantor embedded in reff_id: IV-KKYYMM...)
        if ($request->filled('kantor')) {
            $kodeKantor = str_pad($request->kantor, 2, '0', STR_PAD_LEFT);
            $query->where('reff_id', 'like', "IV-{$kodeKantor}%");
        }

        // Filter by periode (bulan + tahun) via batch
        if ($request->filled('bulan') && $request->filled('tahun')) {
            $periodeYm = $request->tahun . str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
            $batchIds = PenyusutanBatch::where('periode_ym', $periodeYm)->pluck('id');
            $query->whereIn('batch_id', $batchIds);
        } elseif ($request->filled('tahun')) {
            $batchIds = PenyusutanBatch::where('periode_ym', 'like', $request->tahun . '%')->pluck('id');
            $query->whereIn('batch_id', $batchIds);
        }

        // Filter by state
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        // Search by reff_id or core_reff
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reff_id', 'like', "%{$search}%")
                  ->orWhere('core_reff', 'like', "%{$search}%");
            });
        }

        $journals = $query->latest()->paginate(20)->withQueryString();

        // Summary: total amount dari filtered journals
        $summaryQuery = clone $query;
        $summary = $summaryQuery->get()->map(function ($j) {
            $payload = $j->payload;
            return [
                'amount' => (float) str_replace(',', '.', $payload['amount'] ?? '0'),
            ];
        });
        $totalAmount = $summary->sum('amount');
        $totalJournals = $summary->count();

        // Data for filters
        $kantors = MstKantor::orderBy('kode')->get();
        $states = JournalState::cases();

        return view('journals.index', compact(
            'journals', 'kantors', 'states',
            'totalAmount', 'totalJournals'
        ));
    }

    public function detail($id)
    {
        $journal = ApiJournal::with(['logs' => function($q) {
            $q->latest();
        }])->findOrFail($id);

        return response()->json([
            'reff_id' => $journal->reff_id,
            'state' => $journal->state->value,
            'state_label' => $journal->state->label(),
            'state_color' => $journal->state->color(),
            'core_reff' => $journal->core_reff,
            'payload' => $journal->payload,
            'response_body' => $journal->response_body ? json_decode($journal->response_body, true) : null,
            'created_at' => $journal->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $journal->updated_at->format('d/m/Y H:i:s'),
            'logs' => $journal->logs->map(function($log) {
                return [
                    'id' => $log->id,
                    'endpoint' => $log->endpoint,
                    'method' => $log->method,
                    'http_status' => $log->http_status,
                    'duration_ms' => $log->duration_ms,
                    'request_payload' => $log->request_payload,
                    'response_payload' => $log->response_payload,
                    'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                ];
            }),
        ]);
    }

    public function retry($id)
    {
        $journal = ApiJournal::findOrFail($id);
        
        if (!in_array($journal->state->value, ['FAILED', 'DRAFT'])) {
            return redirect()->back()->with('error', 'Hanya jurnal dengan status FAILED atau DRAFT yang bisa di-retry.');
        }

        try {
            \App\Services\FinCloudApiService::sendJournal($journal);
            return redirect()->back()->with('success', 'Jurnal berhasil diproses ulang. Silakan cek status terbarunya.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses ulang jurnal: ' . $e->getMessage());
        }
    }

    /**
     * Export jurnal API ke PDF atau Excel
     */
    public function export(Request $request)
    {
        $query = ApiJournal::with('batch');

        // Apply same filters as index
        if ($request->filled('kantor')) {
            $kodeKantor = str_pad($request->kantor, 2, '0', STR_PAD_LEFT);
            $query->where('reff_id', 'like', "IV-{$kodeKantor}%");
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $periodeYm = $request->tahun . str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
            $batchIds = PenyusutanBatch::where('periode_ym', $periodeYm)->pluck('id');
            $query->whereIn('batch_id', $batchIds);
        } elseif ($request->filled('tahun')) {
            $batchIds = PenyusutanBatch::where('periode_ym', 'like', $request->tahun . '%')->pluck('id');
            $query->whereIn('batch_id', $batchIds);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reff_id', 'like', "%{$search}%")
                  ->orWhere('core_reff', 'like', "%{$search}%");
            });
        }

        $journals = $query->latest()->get();

        // Resolve kantor name for the header
        $kantor = null;
        if ($request->filled('kantor')) {
            $kantor = MstKantor::where('kode', $request->kantor)->first();
        }

        // Build periode label
        $periodeLabel = 'Semua Periode';
        if ($request->filled('bulan') && $request->filled('tahun')) {
            $bulanNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
            $periodeLabel = ($bulanNames[(int)$request->bulan] ?? '?') . ' ' . $request->tahun;
        } elseif ($request->filled('tahun')) {
            $periodeLabel = 'Tahun ' . $request->tahun;
        }

        $format = $request->get('format', 'pdf');
        $isExcel = $format === 'excel';

        if ($isExcel) {
            $filename = "Jurnal_API_" . ($kantor ? $kantor->kode : 'ALL') . "_" . now()->format('Ymd_His') . ".xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");
        }

        return view('journals.export', compact('journals', 'kantor', 'periodeLabel', 'isExcel'));
    }
}
