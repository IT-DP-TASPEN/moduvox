<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\LogsActivity;

class Inquiry extends Component
{
    use WithPagination, LogsActivity;

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Daftar Inventaris');
    }

    public $search = '';
    public $filterCategory = '';
    public $filterStatus = '';
    public $filterBranch = '';
    public $viewMode = 'grid';
    public $selectedAsset = null;

    public function viewAsset(int $id): void
    {
        $this->selectedAsset = Asset::with(['category', 'branch', 'depreciations', 'rentals.rekanan'])->findOrFail($id);
        $this->viewMode = 'detail';
    }

    public function closeView(): void
    {
        $this->selectedAsset = null;
        $this->viewMode = 'grid';
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function inventoryRow(Asset $asset, int $number): array
    {
        $usefulLifeMonths = (int) ($asset->useful_life_months ?: (($asset->useful_life_years ?: 0) * 12));
        $endDate = $asset->purchase_date && $usefulLifeMonths > 0
            ? $asset->purchase_date->copy()->addMonthsNoOverflow($usefulLifeMonths)
            : null;
        $purchasePrice = (float) $asset->purchase_price;
        $currentBookValue = (float) ($asset->current_book_value ?? $purchasePrice);
        $accumulatedDepreciation = (float) $asset->accumulated_depreciation;
        $latestDepreciation = $asset->depreciations->first();
        $depreciationAmount = $latestDepreciation ? (float) $latestDepreciation->depreciation_amount : 0.0;
        $previousBookValue = $latestDepreciation
            ? round((float) $latestDepreciation->book_value_after + $depreciationAmount, 2)
            : $currentBookValue;

        return [
            $number,
            $asset->serial_number ?: $asset->asset_code,
            $asset->name,
            $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-',
            $usefulLifeMonths,
            $endDate ? $endDate->format('d/m/Y') : '-',
            (float) $asset->purchase_price,
            $previousBookValue,
            $depreciationAmount,
            $accumulatedDepreciation,
            $currentBookValue,
        ];
    }

    public function render()
    {
        $assets = Asset::with(['category', 'branch', 'depreciations'])
            ->when($this->search, fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('asset_code', 'like', "%{$this->search}%")
                ->orWhere('serial_number', 'like', "%{$this->search}%")
            )
            ->when($this->filterCategory, fn($q) => $q->where('asset_category_id', $this->filterCategory))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterBranch, fn($q) => $q->where('branch_id', $this->filterBranch))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.assets.inquiry', [
            'assets'     => $assets,
            'categories' => AssetCategory::where('is_active', true)->orderBy('name')->get(),
            'branches'   => Branch::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
