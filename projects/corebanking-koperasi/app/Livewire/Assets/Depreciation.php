<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\AssetCategory;
use App\Services\AssetOperationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

use App\Traits\LogsActivity;

class Depreciation extends Component
{
    use LogsActivity;

    public $period; // YYYY-MM
    public $filterCategory = '';
    public $previewList = [];
    public $isPreviewed = false;
    public $isProcessing = false;

    public function mount(): void
    {
        $this->period = now()->format('Y-m');
        $this->logActivity('NAVIGATE', 'Penyusutan Aset');
    }

    public function preview(): void
    {
        $this->validate(['period' => 'required|date_format:Y-m']);

        $query = Asset::with('category')
            ->whereIn('status', ['ACTIVE', 'RENTED'])
            ->where('current_book_value', '>', 0)
            ->when($this->filterCategory, fn($q) => $q->where('asset_category_id', $this->filterCategory));

        $assets = $query->get();

        $preview = [];
        foreach ($assets as $asset) {
            // Skip if already depreciated for this period
            $alreadyDone = AssetDepreciation::where('asset_id', $asset->id)
                ->where('period_year_month', $this->period)
                ->exists();

            if ($alreadyDone) {
                continue;
            }

            $depreciationAmount = $asset->calculateDepreciation();
            if ($depreciationAmount <= 0) {
                continue;
            }

            $preview[] = [
                'asset_id'            => $asset->id,
                'asset_code'          => $asset->asset_code,
                'name'                => $asset->name,
                'category'            => $asset->category->name ?? '-',
                'status'              => $asset->status,
                'method'              => $asset->depreciation_method,
                'opening_book_value'  => $asset->current_book_value,
                'depreciation_amount' => $depreciationAmount,
                'closing_book_value'  => $asset->current_book_value - $depreciationAmount,
                'rate_or_nominal'     => $asset->depreciation_method === 'PERCENTAGE'
                    ? $asset->getEffectiveMonthlyRate()
                    : $asset->getEffectiveMonthlyNominal(),
            ];
        }

        $this->previewList = $preview;
        $this->isPreviewed = true;
    }

    public function execute(): void
    {
        if (empty($this->previewList)) {
            session()->flash('error', 'Tidak ada aset yang bisa diproses.');
            return;
        }

        $this->isProcessing = true;

        $this->logActivity('BATCH_DEPRECIATION', "Eksekusi penyusutan batch periode {$this->period} untuk " . count($this->previewList) . " aset.");

        $count = app(AssetOperationService::class)
            ->executeBatchDepreciation($this->previewList, $this->period);

        $this->previewList = [];
        $this->isPreviewed = false;
        $this->isProcessing = false;

        session()->flash('success', "Penyusutan berhasil dieksekusi untuk {$count} aset pada periode {$this->period}.");
    }

    public function render()
    {
        return view('livewire.assets.depreciation', [
            'categories' => \App\Models\AssetCategory::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
