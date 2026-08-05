<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Branch;
use App\Models\Coa;
use App\Services\AssetOperationService;
use App\Services\SettlementEngine;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Traits\LogsActivity;

class Create extends Component
{
    use LogsActivity;

    public $name;
    public $asset_category_id;
    public $branch_id;
    public $purchase_date;
    public $purchase_price = 0;
    public $salvage_value = 0;
    public $serial_number;
    public $location;
    public $vendor;
    public $condition = 'GOOD';
    public $description;
    public $payment_channel = 'CASH';
    public $cash_coa_id = null;
    public $bank_coa_id = null;
    public $manual_coa_id = null;

    public function mount()
    {
        $this->branch_id = Auth::user()->branch_id;
        $this->purchase_date = now()->format('Y-m-d');

        $this->logActivity('NAVIGATE', 'Tambah Aset Baru');
    }

    protected function rules(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'asset_category_id'     => 'required|exists:asset_categories,id',
            'branch_id'             => 'required|exists:branches,id',
            'purchase_date'         => 'required|date',
            'purchase_price'        => 'required|numeric|min:0',
            'salvage_value'         => 'nullable|numeric|min:0',
            'serial_number'         => 'nullable|string|max:100',
            'location'              => 'nullable|string|max:255',
            'vendor'                => 'nullable|string|max:255',
            'condition'             => 'required|in:GOOD,FAIR,POOR',
            'description'           => 'nullable|string',
            'payment_channel'       => 'required|in:CASH,ABA,COA',
            'cash_coa_id'           => 'nullable|exists:coas,id',
            'bank_coa_id'           => 'nullable|exists:coas,id',
            'manual_coa_id'         => 'nullable|exists:coas,id',
        ];
    }

    public function updatedPaymentChannel($channel): void
    {
        $this->cash_coa_id = null;
        $this->bank_coa_id = null;
        $this->manual_coa_id = null;

        $options = SettlementEngine::getSelectableCoas($channel);
        if ($options->count() === 1) {
            if ($channel === 'CASH') {
                $this->cash_coa_id = $options->first()->id;
            } elseif ($channel === 'ABA') {
                $this->bank_coa_id = $options->first()->id;
            }
        }
    }

    public function save()
    {
        $this->validate();

        // Validation passed. If fields are empty, they will fallback to category defaults in the model logic.

        // Generate asset code
        $prefix = 'AST-' . now()->format('Y') . '-';
        $lastCode = Asset::where('asset_code', 'like', $prefix . '%')->count() + 1;
        $assetCode = $prefix . str_pad($lastCode, 4, '0', STR_PAD_LEFT);

        $price = (float) str_replace('.', '', $this->purchase_price);
        $salvageValue = (float) str_replace('.', '', $this->salvage_value) ?: 0;
        $category = AssetCategory::with('parent')->find($this->asset_category_id);
        $creditCoaId = $this->selectedCreditCoaId();
        $depreciation = $this->depreciationConfig($category, $price, $salvageValue);

        $data = [
            'asset_code'            => $assetCode,
            'name'                  => $this->name,
            'asset_category_id'     => $this->asset_category_id,
            'branch_id'             => $this->branch_id,
            'purchase_date'         => $this->purchase_date,
            'purchase_price'        => $price,
            'salvage_value'         => $salvageValue,
            'useful_life_months'    => $depreciation['useful_life_months'],
            'useful_life_years'     => $depreciation['useful_life_years'],
            'depreciation_method'   => $depreciation['method'],
            'depreciation_rate'     => $depreciation['rate'],
            'depreciation_nominal'  => $depreciation['nominal'],
            'current_book_value'    => $price,
            'serial_number'         => $this->serial_number,
            'location'              => $this->location,
            'vendor'                => $this->vendor,
            'condition'             => $this->condition,
            'description'           => $this->description,
            'status'                => 'PENDING',
            'created_by'            => Auth::id(),
            'updated_by'            => Auth::id(),
            'asset_purchase_channel' => $this->payment_channel,
            'asset_purchase_credit_coa_id' => $creditCoaId,
        ];

        // Approval Interception
        $asset = new Asset();
        $status = $asset->interceptAction('assets.create', 'CREATE', $data);

        if ($status === 'APPROVED') {
            $assetData = $this->assetData($data);
            $assetData['status'] = 'ACTIVE';
            $asset = Asset::create($assetData);
            
            // Posting jurnal pembelian aset jika langsung disetujui
            app(AssetOperationService::class)->postAssetPurchaseJournal($asset, $creditCoaId);
            
            $asset->logActivity('CREATE', "Mendaftarkan aset baru: {$asset->asset_code}", $asset);
            session()->flash('success', 'Aset berhasil didaftarkan dan diaktifkan.');
        } else {
            session()->flash('success', 'Pendaftaran aset telah dikirim untuk persetujuan.');
        }

        return redirect()->route('assets.inquiry');
    }

    public function render()
    {
        return view('livewire.assets.create', [
            'categories' => AssetCategory::with('children')->root()->where('is_active', true)->orderBy('name')->get(),
            'branches'   => Branch::where('is_active', true)->orderBy('name')->get(),
            'cashCoas'   => SettlementEngine::getSelectableCoas('CASH'),
            'abaCoas'    => SettlementEngine::getSelectableCoas('ABA'),
            'manualCoas' => Coa::active()->leaf()->orderBy('coa_code')->get(),
        ])->layout('layouts.app');
    }

    private function selectedCreditCoaId(): ?int
    {
        return match ($this->payment_channel) {
            'CASH' => $this->cash_coa_id ? (int) $this->cash_coa_id : null,
            'ABA' => $this->bank_coa_id ? (int) $this->bank_coa_id : null,
            'COA' => $this->manual_coa_id ? (int) $this->manual_coa_id : throw \Illuminate\Validation\ValidationException::withMessages([
                'manual_coa_id' => 'COA manual wajib dipilih.',
            ]),
            default => null,
        };
    }

    private function assetData(array $data): array
    {
        unset($data['asset_purchase_channel'], $data['asset_purchase_credit_coa_id']);

        return $data;
    }

    private function depreciationConfig(?AssetCategory $category, float $price, float $salvageValue): array
    {
        $months = (int) ($category?->getEffectiveRule('useful_life_months')
            ?: ((int) ($category?->getEffectiveRule('useful_life_years') ?: 0) * 12));
        $method = $category?->getEffectiveRule('depreciation_method') ?: 'STRAIGHT_LINE';

        return [
            'useful_life_months' => $months ?: null,
            'useful_life_years' => $months ? max(1, (int) ceil($months / 12)) : null,
            'method' => $method,
            'rate' => $months ? round(100 / $months, 6) : null,
            'nominal' => $method === 'STRAIGHT_LINE' && $months
                ? round(max(0, $price - $salvageValue) / $months, 2)
                : null,
        ];
    }
}
