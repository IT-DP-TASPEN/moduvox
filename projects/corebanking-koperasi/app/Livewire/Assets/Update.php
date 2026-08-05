<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Branch;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Update extends Component
{
    use WithPagination, ApprovesActions, LogsActivity;

    public string $search = '';
    public ?int $selectedAssetId = null;
    public ?Asset $asset = null;

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

    public function mount(): void
    {
        $this->logActivity('NAVIGATE', 'Perubahan Inventaris');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function selectAsset(int $id): void
    {
        $this->asset = Asset::with('category')->findOrFail($id);
        $this->selectedAssetId = $id;

        $this->name = $this->asset->name;
        $this->asset_category_id = $this->asset->asset_category_id;
        $this->branch_id = $this->asset->branch_id;
        $this->purchase_date = $this->asset->purchase_date?->format('Y-m-d');
        $this->purchase_price = (float) $this->asset->purchase_price;
        $this->salvage_value = (float) $this->asset->salvage_value;
        $this->serial_number = $this->asset->serial_number;
        $this->location = $this->asset->location;
        $this->vendor = $this->asset->vendor;
        $this->condition = $this->asset->condition;
        $this->description = $this->asset->description;
    }

    public function closeForm(): void
    {
        $this->reset([
            'selectedAssetId', 'asset', 'name', 'asset_category_id', 'branch_id',
            'purchase_date', 'purchase_price', 'salvage_value', 'serial_number',
            'location', 'vendor', 'condition', 'description',
        ]);
    }

    public function save()
    {
        $this->validate([
            'selectedAssetId' => 'required|exists:assets,id',
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'branch_id' => 'required|exists:branches,id',
            'purchase_date' => 'required|date',
            'purchase_price' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'condition' => 'required|in:GOOD,FAIR,POOR',
            'description' => 'nullable|string',
        ]);

        $asset = Asset::findOrFail($this->selectedAssetId);
        $category = AssetCategory::with('parent')->findOrFail($this->asset_category_id);
        $purchasePrice = (float) $this->purchase_price;
        $salvageValue = (float) ($this->salvage_value ?: 0);
        $usefulLifeMonths = (int) ($category->getEffectiveRule('useful_life_months') ?: 0);
        $method = $category->getEffectiveRule('depreciation_method') ?: 'STRAIGHT_LINE';
        $bookValue = max($salvageValue, $purchasePrice - (float) $asset->accumulated_depreciation);

        $data = [
            'name' => $this->name,
            'asset_category_id' => $this->asset_category_id,
            'branch_id' => $this->branch_id,
            'purchase_date' => $this->purchase_date,
            'purchase_price' => $purchasePrice,
            'current_value' => $bookValue,
            'current_book_value' => $bookValue,
            'salvage_value' => $salvageValue,
            'useful_life_months' => $usefulLifeMonths ?: null,
            'useful_life_years' => $usefulLifeMonths ? max(1, (int) ceil($usefulLifeMonths / 12)) : null,
            'depreciation_method' => $method,
            'depreciation_rate' => $method === 'PERCENTAGE' && $usefulLifeMonths ? round(100 / $usefulLifeMonths, 6) : null,
            'depreciation_nominal' => $method === 'STRAIGHT_LINE' && $usefulLifeMonths
                ? round(max(0, $purchasePrice - $salvageValue) / $usefulLifeMonths, 2)
                : null,
            'serial_number' => $this->serial_number,
            'location' => $this->location,
            'vendor' => $this->vendor,
            'condition' => $this->condition,
            'description' => $this->description,
            'updated_by' => Auth::id(),
        ];

        $status = $this->interceptAction('assets.update', 'UPDATE', $data, $asset->id, $asset->only(array_keys($data)));

        if ($status === 'PENDING') {
            session()->flash('success', 'Perubahan inventaris telah dikirim untuk persetujuan.');
            return redirect()->route('assets.inquiry');
        }

        $asset->update($data);
        $this->logActivity('UPDATE', "Mengubah inventaris [{$asset->asset_code}]", $asset);
        session()->flash('success', 'Data inventaris berhasil diperbarui.');

        return redirect()->route('assets.inquiry');
    }

    public function render()
    {
        $assets = strlen(trim($this->search)) >= 2
            ? Asset::with(['category', 'branch'])
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('asset_code', 'like', "%{$this->search}%")
                    ->orWhere('serial_number', 'like', "%{$this->search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            : Asset::whereRaw('1 = 0')->paginate(10);

        return view('livewire.assets.update', [
            'assets' => $assets,
            'categories' => AssetCategory::with('parent')->where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
