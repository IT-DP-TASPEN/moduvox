<?php

namespace App\Livewire\InsuranceProducts;

use App\Models\InsuranceProduct;
use App\Models\InsuranceRate;
use App\Traits\LogsActivity;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InsuranceRateExport;
use App\Imports\InsuranceRateImport;

class RateManager extends Component
{
    use WithPagination, WithFileUploads, LogsActivity;

    public $productId;
    public $searchAge = '';
    public $maxYear = 0; // Will be auto-detected in render
    public $sortDir = 'asc';
    public $hideZeros = false;
    public $importFile;
    public $editingCell = null; // 'age-tenor'
    public $editValue = '';

    public function mount($id)
    {
        $this->productId = $id;
        $this->logActivity('NAVIGATE', 'Produk Asuransi');
    }

    public function startEdit($age, $year, $currentValue)
    {
        $this->editingCell = "{$age}-{$year}";
        $this->editValue = $currentValue;
    }

    public function saveEdit($age, $year)
    {
        $this->validate(['editValue' => 'required|numeric|min:0|max:100']);

        InsuranceRate::updateOrCreate(
            [
                'insurance_product_id' => $this->productId,
                'age' => $age,
                'tenor_months' => $year * 12,
            ],
            ['rate' => (float)$this->editValue]
        );

        $this->logActivity('UPDATE_INSURANCE_RATE', "Memperbarui tarif asuransi: Usia {$age}, JKW {$year} Tahun, Rate: {$this->editValue}", null, ['age' => $age, 'year' => $year, 'rate' => $this->editValue]);

        $this->editingCell = null;
        $this->editValue = '';
        session()->flash('success', "Tarif untuk Usia {$age} JKW {$year} Thn berhasil diperbarui.");
    }

    public function addYear()
    {
        $this->maxYear++;
        $this->logActivity('ADD_INSURANCE_YEAR', "Menambahkan kolom JKW {$this->maxYear} Tahun pada matrix asuransi", null, ['year' => $this->maxYear]);
        session()->flash('success', "Kolom JKW {$this->maxYear} Tahun berhasil ditambahkan.");
    }

    public function addAge()
    {
        $this->validate(['searchAge' => 'required|integer|min:1|max:100']);

        // Create at least JKW 1 for this age to make it appear in the grid
        InsuranceRate::firstOrCreate([
            'insurance_product_id' => $this->productId,
            'age' => (int)$this->searchAge,
            'tenor_months' => 12,
        ], ['rate' => 0]);

        $this->logActivity('ADD_INSURANCE_AGE', "Menambahkan baris usia {$this->searchAge} pada matrix asuransi", null, ['age' => $this->searchAge]);

        $this->searchAge = '';
        session()->flash('success', "Baris usia baru berhasil ditambahkan.");
    }

    public function initializeMatrix()
    {
        $product = InsuranceProduct::findOrFail($this->productId);

        $batch = [];
        for ($age = 17; $age <= 80; $age++) {
            for ($year = 1; $year <= $this->maxYear; $year++) {
                $batch[] = [
                    'insurance_product_id' => $this->productId,
                    'age' => $age,
                    'tenor_months' => $year * 12,
                    'rate' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        InsuranceRate::insert($batch);
        $this->logActivity('INITIALIZE_INSURANCE_MATRIX', "Menginisialisasi matrix asuransi: Usia 17-80, JKW 1-{$this->maxYear}", $product, ['max_year' => $this->maxYear]);
        session()->flash('success', "Matrix berhasil diinisialisasi (Usia 17-80, JKW 1-{$this->maxYear}). Silakan isi tarif pada sel yang tersedia.");
    }

    public function importMatrix()
    {
        $this->validate([
            'importFile' => 'required|mimes:csv,xlsx,xls,txt|max:4096',
        ]);

        try {
            Excel::import(new InsuranceRateImport($this->productId), $this->importFile->getRealPath());
            $this->logActivity('IMPORT_INSURANCE_MATRIX', "Mengimpor data matrix tarif asuransi dari file: {$this->importFile->getClientOriginalName()}", null, ['filename' => $this->importFile->getClientOriginalName()]);
            $this->importFile = null;
            session()->flash('success', "Berhasil mengimpor data matrix tarif dari file Excel/CSV.");
        } catch (\Exception $e) {
            session()->flash('error', "Gagal mengimpor: " . $e->getMessage());
        }
    }

    public function clearMatrix()
    {
        InsuranceRate::where('insurance_product_id', $this->productId)->delete();
        $this->logActivity('CLEAR_INSURANCE_MATRIX', "Mengosongkan semua data matrix tarif asuransi");
        session()->flash('success', "Matrix tarif berhasil dikosongkan.");
    }

    public function toggleSort()
    {
        $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
    }

    public function deleteAge($age)
    {
        InsuranceRate::where('insurance_product_id', $this->productId)
            ->where('age', $age)
            ->delete();
        $this->logActivity('DELETE_INSURANCE_AGE', "Menghapus data tarif asuransi untuk usia {$age}", null, ['age' => $age]);
        session()->flash('success', "Data tarif untuk usia {$age} berhasil dihapus.");
    }

    public function exportMatrix()
    {
        $product = InsuranceProduct::findOrFail($this->productId);
        $fileName = 'Matrix_Asuransi_' . str_replace(' ', '_', $product->name) . '_' . now()->format('Ymd_His') . '.xlsx';
        $this->logActivity('EXPORT_INSURANCE_MATRIX', "Mengunduh matrix tarif asuransi: {$fileName}", $product, ['filename' => $fileName]);

        return Excel::download(new InsuranceRateExport($this->productId, $this->maxYear), $fileName);
    }

    public function render()
    {
        $product = InsuranceProduct::findOrFail($this->productId);

        $agesQuery = InsuranceRate::where('insurance_product_id', $this->productId)
            ->select('age')
            ->distinct()
            ->when($this->searchAge, fn($q) => $q->where('age', $this->searchAge))
            ->when($this->hideZeros, function ($q) {
                $q->whereIn('age', function ($sub) {
                    $sub->select('age')
                        ->from('insurance_rates')
                        ->where('insurance_product_id', $this->productId)
                        ->where('rate', '>', 0);
                });
            })
            ->orderBy('age', $this->sortDir);

        // Fix: Manual count for distinct pagination
        $totalCount = $agesQuery->get()->count();
        $paginatedAges = $agesQuery->paginate(15, ['*'], 'page', null, $totalCount);

        $ageList = $paginatedAges->pluck('age')->toArray();

        // Fetch rates for these ages
        $rates = InsuranceRate::where('insurance_product_id', $this->productId)
            ->whereIn('age', $ageList)
            ->get()
            ->groupBy('age')
            ->map(function ($ageGroup) {
                return $ageGroup->keyBy(fn($item) => $item->tenor_months / 12);
            });

        // Determine which years to show: max of property maxYear OR max year in DB for this product
        $dbMaxYear = InsuranceRate::where('insurance_product_id', $this->productId)->max('tenor_months');
        $dbMaxYear = $dbMaxYear ? (int)($dbMaxYear / 12) : 0;

        // If we haven't manually added years and DB has years, use DB. 
        // Default to at least 1 if empty.
        $displayMaxYear = max($this->maxYear, $dbMaxYear, 1);

        return view('livewire.insurance-products.rate-manager', [
            'product' => $product,
            'paginatedAges' => $paginatedAges,
            'rates' => $rates,
            'years' => range(1, $displayMaxYear)
        ])->layout('layouts.app');
    }
}
