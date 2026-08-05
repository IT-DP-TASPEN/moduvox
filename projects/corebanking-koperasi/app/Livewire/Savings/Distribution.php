<?php

namespace App\Livewire\Savings;

use App\Models\SavingDistribution;
use App\Models\SavingProduct;
use App\Models\Coa;
use App\Services\SavingDistributionService;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;
use App\Services\SettlementEngine;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Distribution extends Component
{
    use WithPagination, WithLogout, ApprovesActions, LogsActivity, WithFileUploads;

    public $user, $role;
    public $activeTab = 'form'; // form | history

    // Form fields
    public $distribution_type = 'CREDIT';
    public $channel = 'CASH';
    public $saving_product_id = '';
    public $description = '';
    public $effective_date = '';
    public $bank_coa_id = null;
    public $cash_coa_id = null;
    public $coa_id = null;
    public $coaSearch = '';
    
    // File upload and parsed state
    public $importFile;
    public $parsedItems = [];

    // Preview state
    public $preview = null;
    public $showPreview = false;

    protected $rules = [
        'distribution_type'   => 'required|in:CREDIT,DEBIT',
        'channel'             => 'required|in:CASH,ABA,COA',
        'bank_coa_id'         => 'nullable|exists:coas,id',
        'cash_coa_id'         => 'nullable|exists:coas,id',
        'coa_id'              => 'nullable|exists:coas,id',
        'saving_product_id'   => 'required|exists:saving_products,id',
        'description'         => 'nullable|string|max:255',
        'effective_date'      => 'required|date',
    ];

    public function mount()
    {
        $this->user          = Auth::user();
        $this->role          = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->effective_date = now()->format('Y-m-d');
        $this->logActivity('NAVIGATE', 'Distribusi Dana Simpanan');
    }

    /**
     * Preview distribusi sebelum eksekusi dengan parse CSV.
     */
    public function previewDistribution()
    {
        $this->validate();
        
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $path = $this->importFile->getRealPath();
            $file = fopen($path, 'r');
            
            // Deteksi UTF-8 BOM
            $bom = fread($file, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($file);
            }
            
            // Baca baris pertama untuk deteksi separator
            $firstLine = fgets($file);
            rewind($file);
            if ($bom === "\xEF\xBB\xBF") {
                // Skip BOM again
                fread($file, 3);
            }
            
            $separator = ',';
            if (str_contains($firstLine, ';')) {
                $separator = ';';
            }
            
            $headers = fgetcsv($file, 0, $separator);
            if (!$headers) {
                throw new \Exception("File CSV kosong atau tidak valid.");
            }
            
            $headers = array_map(function($h) {
                return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
            }, $headers);

            // Cari index kolom
            $accountIndex = array_search('account_no', $headers);
            $amountIndex = array_search('amount', $headers);
            $noteIndex = array_search('note', $headers);
            if ($noteIndex === false) {
                $noteIndex = array_search('keterangan', $headers);
            }

            if ($accountIndex === false || $amountIndex === false) {
                throw new \Exception("File CSV harus memiliki kolom header 'account_no' dan 'amount'.");
            }

            $items = [];
            while (($row = fgetcsv($file, 0, $separator)) !== false) {
                // Lewati baris kosong
                if (empty($row) || !isset($row[$accountIndex]) || trim($row[$accountIndex]) === '') {
                    continue;
                }
                
                $items[] = [
                    'account_no' => trim($row[$accountIndex]),
                    'amount'     => (float) str_replace(',', '.', trim($row[$amountIndex])),
                    'note'       => $noteIndex !== false && isset($row[$noteIndex]) ? trim($row[$noteIndex]) : '',
                ];
            }
            fclose($file);

            if (empty($items)) {
                throw new \Exception("File CSV tidak berisi data transaksi yang valid.");
            }

            $this->parsedItems = $items;

            $service = app(SavingDistributionService::class);
            $coaOverrideId = $this->selectedSettlementCoaId();
            if ($this->channel === 'COA' && !$coaOverrideId) {
                $this->addError('coa_id', 'Pilih COA untuk jalur transaksi ini.');
                return;
            }

            $this->preview = $service->preview(
                productId: (int) $this->saving_product_id,
                items: $this->parsedItems,
                type: $this->distribution_type,
                channel: $this->channel,
                coaOverrideId: $coaOverrideId,
            );
            $this->showPreview = true;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->showPreview = false;
            $this->preview = null;
        }
    }

    /**
     * Unduh contoh template CSV.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=template_distribusi_simpanan.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['account_no', 'amount', 'note']);
            fputcsv($file, ['1010000001', '50000', 'Bonus Sukarela Tahunan']);
            fputcsv($file, ['1010000002', '75000', 'Bonus Anggota Baru']);
            fclose($file);
        };

        return response()->streamDownload($callback, 'template_distribusi_simpanan.csv', $headers);
    }

    /**
     * Submit distribusi (dengan atau tanpa approval).
     */
    public function submitDistribution()
    {
        $this->validate();

        if (empty($this->parsedItems)) {
            session()->flash('error', 'Silakan unggah dan preview file distribusi terlebih dahulu.');
            return;
        }

        $coaOverrideId = $this->selectedSettlementCoaId();
        if ($this->channel === 'COA' && !$coaOverrideId) {
            $this->addError('coa_id', 'Pilih COA untuk jalur transaksi ini.');
            return;
        }

        $data = [
            'distribution_no'    => SavingDistributionService::generateDistributionNo(),
            'distribution_type'  => $this->distribution_type,
            'channel'            => $this->channel,
            'coa_override_id'    => $coaOverrideId,
            'saving_product_id'  => (int) $this->saving_product_id,
            'description'        => $this->description,
            'effective_date'     => $this->effective_date,
            'items'              => $this->parsedItems,
        ];

        $status = $this->interceptAction('savings.distribution', 'DISTRIBUTE', $data);

        if ($status === 'PENDING') {
            $this->logActivity('DISTRIBUTE_REQUEST', 'Mengajukan distribusi dana simpanan massal ke approval');
            session()->flash('success', 'Permintaan distribusi dana berhasil diajukan ke antrean persetujuan.');
            $this->resetForm();
            $this->activeTab = 'history';
            return;
        }

        $this->executeDistribution($data);
    }

    /**
     * Eksekusi langsung (dipanggil juga dari approval handler).
     */
    public function executeDistribution(array $data)
    {
        try {
            $service = app(SavingDistributionService::class);
            $dist = $service->executeDistribution($data);

            $typeLabel = $data['distribution_type'] === 'CREDIT' ? 'Kredit' : 'Debit';
            $this->logActivity(
                'DISTRIBUTE',
                "Distribusi {$typeLabel} Massal via Upload — {$dist->account_count} rekening — Rp " . number_format($dist->total_amount, 0, ',', '.')
            );

            session()->flash('success', "Distribusi berhasil! {$dist->account_count} rekening telah diproses. Total: Rp " . number_format($dist->total_amount, 0, ',', '.'));
            $this->resetForm();
            $this->activeTab = 'history';

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengeksekusi distribusi: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset(['saving_product_id', 'description', 'preview', 'showPreview', 'importFile', 'parsedItems', 'bank_coa_id', 'cash_coa_id', 'coa_id', 'coaSearch']);
        $this->distribution_type = 'CREDIT';
        $this->channel           = 'CASH';
        $this->effective_date    = now()->format('Y-m-d');
    }

    public function updatedDistributionType()
    {
        $this->showPreview = false;
        $this->preview = null;
        $this->parsedItems = [];
    }

    public function updatedChannel($channel)
    {
        $this->showPreview = false;
        $this->preview = null;
        $this->parsedItems = [];
        $this->bank_coa_id = null;
        $this->cash_coa_id = null;
        $this->coa_id = null;
        $this->coaSearch = '';

        if ($channel === 'COA') {
            return;
        }

        $options = SettlementEngine::getSelectableCoas($channel);
        if ($options->count() === 1) {
            if ($channel === 'ABA') {
                $this->bank_coa_id = $options->first()->id;
            } else {
                $this->cash_coa_id = $options->first()->id;
            }
        }
    }

    public function updatedCoaSearch($value)
    {
        $this->coa_id = null;
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $code = str_contains($value, ' - ') ? trim(strtok($value, ' - ')) : $value;
        $coa = Coa::active()
            ->leaf()
            ->where(function ($query) use ($value, $code) {
                $query->where('coa_code', $code)
                    ->orWhere('coa_code', $value)
                    ->orWhereRaw("CONCAT(coa_code, ' - ', name) = ?", [$value]);
            })
            ->first();

        if ($coa) {
            $this->coa_id = $coa->id;
            $this->coaSearch = "{$coa->coa_code} - {$coa->name}";
        }
    }

    public function updatedSavingProductId()
    {
        $this->showPreview = false;
        $this->preview = null;
        $this->parsedItems = [];
    }

    public function render()
    {
        $products = SavingProduct::where('is_active', true)->orderBy('name')->get();

        $histories = SavingDistribution::with(['product', 'counterpartCoa', 'creator'])
            ->latest()
            ->paginate(10);

        $abaCoas  = SettlementEngine::getSelectableCoas('ABA');
        $cashCoas = SettlementEngine::getSelectableCoas('CASH');
        $allCoas = $this->coaOptions();

        return view('livewire.savings.distribution', [
            'products'  => $products,
            'histories' => $histories,
            'abaCoas'   => $abaCoas,
            'cashCoas'  => $cashCoas,
            'allCoas'   => $allCoas,
        ])->layout('layouts.app');
    }

    private function selectedSettlementCoaId(): ?int
    {
        $channel = SettlementEngine::normalizeChannel($this->channel);

        if ($this->channel === 'COA') {
            $selectedId = $this->coa_id ? (int) $this->coa_id : null;
            return $selectedId && Coa::active()->leaf()->whereKey($selectedId)->exists() ? $selectedId : null;
        }

        $options = SettlementEngine::getSelectableCoas($channel);

        if ($options->isEmpty()) {
            return null;
        }

        if ($channel === SettlementEngine::CHANNEL_ABA) {
            $selectedId = $this->bank_coa_id ? (int) $this->bank_coa_id : null;
            return $selectedId && $options->contains('id', $selectedId) ? $selectedId : null;
        }

        $selectedId = $this->cash_coa_id ? (int) $this->cash_coa_id : null;
        return $selectedId && $options->contains('id', $selectedId) ? $selectedId : null;
    }

    private function coaOptions()
    {
        $search = trim($this->coaSearch);

        $query = Coa::active()->leaf()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('coa_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('coa_code')
            ->limit(50);

        $options = $query->get();

        if ($this->coa_id && !$options->contains('id', (int) $this->coa_id)) {
            $selected = Coa::active()->leaf()->whereKey($this->coa_id)->first();
            if ($selected) {
                $options->prepend($selected);
            }
        }

        return $options;
    }
}
