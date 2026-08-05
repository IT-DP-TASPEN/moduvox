<?php

namespace App\Livewire\AssetRentals;

use App\Models\AssetRentalBilling;
use App\Models\Coa;
use App\Services\AssetOperationService;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PaymentImport extends Component
{
    use WithFileUploads, WithPagination, LogsActivity, ApprovesActions;

    public $activeTab = 'form';
    public $importFile;
    public $parsedItems = [];
    public $preview = null;
    public $showPreview = false;

    public function mount(): void
    {
        $this->logActivity('NAVIGATE', 'Pembayaran Sewa Aset Masal');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=template_pembayaran_sewa_aset.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['contract_no', 'billing_period', 'amount', 'note']);
            fputcsv($file, ['KSW-202601-001', '2026-02', '4200000', 'Pembayaran sewa Februari 2026']);
            fputcsv($file, ['KSW-202601-001', '2026-03', '4200000', 'Pembayaran sewa Maret 2026']);
            fclose($file);
        };

        return response()->streamDownload($callback, 'template_pembayaran_sewa_aset.csv', $headers);
    }

    public function previewImport(): void
    {
        $this->validate(['importFile' => 'required|file|mimes:csv,txt|max:2048']);

        try {
            $items = $this->parseCsv();
            [$debitCoa, $creditCoa] = $this->defaultPaymentCoas();
            $warnings = [];
            $rows = [];
            $total = 0;

            foreach ($items as $item) {
                $billing = AssetRentalBilling::with('rental.asset', 'rental.rekanan')
                    ->where('billing_period', $item['billing_period'])
                    ->whereHas('rental', fn ($query) => $query->where('contract_no', $item['contract_no']))
                    ->first();

                $statusText = 'OK';
                if (!$billing) {
                    $statusText = 'Tidak ditemukan';
                    $warnings[] = "Baris {$item['row']}: tagihan {$item['contract_no']} periode {$item['billing_period']} tidak ditemukan.";
                } elseif ($billing->status === 'PAID') {
                    $statusText = 'Sudah lunas';
                    $warnings[] = "Baris {$item['row']}: tagihan sudah lunas.";
                } elseif (abs((float) $billing->amount - (float) $item['amount']) > 0.01) {
                    $statusText = 'Nominal beda';
                    $warnings[] = "Baris {$item['row']}: nominal tidak cocok dengan tagihan sistem.";
                } else {
                    $total += (float) $billing->amount;
                }

                $rows[] = [
                    ...$item,
                    'asset_name' => $billing?->rental?->asset?->name ?? '-',
                    'rekanan_name' => $billing?->rental?->rekanan?->name ?? '-',
                    'system_amount' => $billing ? (float) $billing->amount : 0,
                    'billing_status' => $billing?->status ?? '-',
                    'status_text' => $statusText,
                ];
            }

            $this->parsedItems = $items;
            $this->preview = [
                'items' => $rows,
                'warnings' => $warnings,
                'has_warnings' => !empty($warnings),
                'count' => count($items),
                'valid_count' => count($items) - count($warnings),
                'total' => $total,
                'debit_coa' => "{$debitCoa->coa_code} - {$debitCoa->name}",
                'credit_coa' => "{$creditCoa->coa_code} - {$creditCoa->name}",
            ];
            $this->showPreview = true;
        } catch (\Exception $e) {
            $this->reset(['parsedItems', 'preview', 'showPreview']);
            session()->flash('error', $e->getMessage());
        }
    }

    public function submitImport(AssetOperationService $assetService): void
    {
        if (!$this->preview || $this->preview['has_warnings'] || empty($this->parsedItems)) {
            session()->flash('error', 'Preview harus valid sebelum import dieksekusi.');
            return;
        }

        try {
            [$debitCoa, $creditCoa] = $this->defaultPaymentCoas();
            $data = [
                'bulk_payments' => $this->parsedItems,
                'payment_debit_coa_id' => $debitCoa->id,
                'payment_credit_coa_id' => $creditCoa->id,
            ];

            $status = $this->interceptAction('asset-rentals.index', 'UPDATE', $data);
            if ($status === 'PENDING') {
                $this->resetForm();
                $this->activeTab = 'history';
                $this->logActivity('UPDATE_REQUEST', 'Mengajukan pembayaran tagihan sewa aset massal');
                session()->flash('success', 'Import pembayaran sewa aset diajukan ke antrean persetujuan.');
                return;
            }

            $result = $assetService->recognizeRentalRevenueBulk($data, Auth::id());
            $this->resetForm();
            $this->activeTab = 'history';
            $this->logActivity('UPDATE', "Mencatat pembayaran sewa aset massal {$result['count']} tagihan");
            session()->flash('success', "Berhasil menandai {$result['count']} tagihan lunas. Total: Rp " . number_format($result['total'], 2, ',', '.'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function resetForm(): void
    {
        $this->reset(['importFile', 'parsedItems', 'preview', 'showPreview']);
    }

    private function defaultPaymentCoas(): array
    {
        $debitCoa = Coa::active()->leaf()->where('type', 'LIABILITY')->where('coa_code', '219011')->first();
        $creditCoa = Coa::active()->leaf()->where('type', 'REVENUE')->where('coa_code', '417000')->first();

        if (!$debitCoa) {
            throw new \Exception('COA 219011 - Titipan Jasa Sewa belum tersedia/aktif.');
        }

        if (!$creditCoa) {
            throw new \Exception('COA 417000 - Pendapatan Sewa Aset belum tersedia/aktif.');
        }

        return [$debitCoa, $creditCoa];
    }

    private function parseCsv(): array
    {
        $file = fopen($this->importFile->getRealPath(), 'r');
        if (!$file) {
            throw new \Exception('File CSV tidak bisa dibaca.');
        }

        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        $firstLine = fgets($file);
        rewind($file);
        if ($bom === "\xEF\xBB\xBF") {
            fread($file, 3);
        }

        $separator = str_contains((string) $firstLine, ';') ? ';' : ',';
        $headers = fgetcsv($file, 0, $separator);
        if (!$headers) {
            fclose($file);
            throw new \Exception('File CSV kosong atau tidak valid.');
        }

        $headers = array_map(fn ($header) => strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string) $header))), $headers);
        $contractIndex = array_search('contract_no', $headers);
        $periodIndex = array_search('billing_period', $headers);
        $amountIndex = array_search('amount', $headers);
        $noteIndex = array_search('note', $headers);

        if ($contractIndex === false || $periodIndex === false || $amountIndex === false) {
            fclose($file);
            throw new \Exception('CSV wajib memiliki header: contract_no, billing_period, amount.');
        }

        $items = [];
        $seen = [];
        $rowNumber = 1;
        while (($row = fgetcsv($file, 0, $separator)) !== false) {
            $rowNumber++;
            $contractNo = trim($row[$contractIndex] ?? '');
            $period = trim($row[$periodIndex] ?? '');
            if ($contractNo === '' && $period === '') {
                continue;
            }

            if ($contractNo === '' || !preg_match('/^\d{4}-\d{2}$/', $period)) {
                fclose($file);
                throw new \Exception("Baris {$rowNumber}: contract_no wajib diisi dan billing_period harus format YYYY-MM.");
            }

            $key = "{$contractNo}|{$period}";
            if (isset($seen[$key])) {
                fclose($file);
                throw new \Exception("Baris {$rowNumber}: duplikat kontrak/periode {$contractNo} {$period}.");
            }
            $seen[$key] = true;

            $items[] = [
                'row' => $rowNumber,
                'contract_no' => $contractNo,
                'billing_period' => $period,
                'amount' => $this->parseMoney($row[$amountIndex] ?? '', $rowNumber),
                'payment_reference' => '',
                'note' => $noteIndex !== false ? trim($row[$noteIndex] ?? '') : '',
            ];
        }
        fclose($file);

        if (empty($items)) {
            throw new \Exception('File CSV tidak berisi pembayaran yang valid.');
        }

        return $items;
    }

    private function parseMoney(mixed $value, int $rowNumber): float
    {
        $value = trim(str_ireplace(['rp', ' '], '', (string) $value));
        if ($value === '') {
            throw new \Exception("Baris {$rowNumber}: amount wajib diisi.");
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = strrpos($value, ',') > strrpos($value, '.')
                ? str_replace(',', '.', str_replace('.', '', $value))
                : str_replace(',', '', $value);
        } elseif (str_contains($value, ',')) {
            $value = preg_match('/,\d{1,2}$/', $value) ? str_replace(',', '.', str_replace('.', '', $value)) : str_replace(',', '', $value);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $value)) {
            $value = str_replace('.', '', $value);
        }

        if (!is_numeric($value)) {
            throw new \Exception("Baris {$rowNumber}: amount harus berupa angka.");
        }

        if ((float) $value <= 0) {
            throw new \Exception("Baris {$rowNumber}: amount harus lebih dari 0.");
        }

        return (float) $value;
    }

    public function render()
    {
        $history = AssetRentalBilling::with('rental.asset', 'rental.rekanan')
            ->where('status', 'PAID')
            ->latest('paid_at')
            ->paginate(15);

        return view('livewire.asset-rentals.payment-import', [
            'history' => $history,
        ])->layout('layouts.app');
    }
}
