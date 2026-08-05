<?php

namespace App\Livewire\Assets;

use App\Models\AssetCategory;
use App\Models\Coa;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\LogsActivity;

class Categories extends Component
{
    use WithPagination, LogsActivity;

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Kategori Aset');
    }

    public $search = '';

    // Create / Edit modal state
    public bool $showModal    = false;
    public ?int $editingId    = null;

    // Form fields
    public ?int    $parent_id                = null;
    public string  $name                     = '';
    public string  $description              = '';
    public bool    $is_active                = true;
    public ?int    $coa_aset_id              = null;
    public ?int    $coa_akum_penyusutan_id   = null;
    public ?int    $coa_beban_penyusutan_id  = null;
    public ?int    $coa_kas_id               = null;
    // Konfigurasi penyusutan per golongan
    public string  $depreciation_method       = 'STRAIGHT_LINE';
    public ?string $depreciation_rate_annual  = null;
    public ?int    $useful_life_months        = null;

    // Delete confirm
    public bool   $confirmingDeletion = false;
    public ?int   $deletingId         = null;
    public string $deletingName       = '';

    protected function rules(): array
    {
        return [
            'parent_id'               => 'nullable|exists:asset_categories,id',
            'name'                    => 'required|string|max:100',
            'description'             => 'nullable|string|max:500',
            'is_active'               => 'boolean',
            'coa_aset_id'             => 'nullable|exists:coas,id',
            'coa_akum_penyusutan_id'  => 'nullable|exists:coas,id',
            'coa_beban_penyusutan_id' => 'nullable|exists:coas,id',
            'coa_kas_id'              => 'nullable|exists:coas,id',
            'depreciation_method'       => 'nullable|in:PERCENTAGE,STRAIGHT_LINE',
            'depreciation_rate_annual'  => 'nullable|numeric|min:0|max:100',
            'useful_life_months'        => 'nullable|integer|min:1|max:600',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId  = null;
        $this->showModal  = true;
    }

    public function openEdit(int $id): void
    {
        $cat = AssetCategory::findOrFail($id);
        $this->editingId                = $cat->id;
        $this->parent_id                = $cat->parent_id;
        $this->name                     = $cat->name;
        $this->description              = $cat->description ?? '';
        $this->is_active                = (bool) $cat->is_active;
        $this->coa_aset_id              = $cat->coa_aset_id;
        $this->coa_akum_penyusutan_id   = $cat->coa_akum_penyusutan_id;
        $this->coa_beban_penyusutan_id  = $cat->coa_beban_penyusutan_id;
        $this->coa_kas_id               = $cat->coa_kas_id;
        $this->depreciation_method      = $cat->depreciation_method ?? '';
        $this->depreciation_rate_annual = $cat->depreciation_rate_annual ? (string) $cat->depreciation_rate_annual : null;
        $this->useful_life_months       = $cat->useful_life_months;
        $this->showModal                = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'parent_id'               => $this->parent_id ?: null,
            'name'                    => $this->name,
            'description'             => $this->description ?: null,
            'is_active'               => $this->is_active,
            'coa_aset_id'             => $this->coa_aset_id ?: null,
            'coa_akum_penyusutan_id'  => $this->coa_akum_penyusutan_id ?: null,
            'coa_beban_penyusutan_id' => $this->coa_beban_penyusutan_id ?: null,
            'coa_kas_id'              => $this->coa_kas_id ?: null,
            'depreciation_method'       => $this->depreciation_method ?: null,
            'depreciation_rate_annual'  => $this->depreciation_rate_annual ?: null,
            'useful_life_months'        => $this->useful_life_months ?: null,
        ];

        if ($this->editingId) {
            $cat = AssetCategory::findOrFail($this->editingId);
            $cat->update($data);
            $this->logActivity('UPDATE', "Memperbarui kategori aset: {$this->name}");
            session()->flash('success', "Kategori \"{$this->name}\" berhasil diperbarui.");
        } else {
            $cat = AssetCategory::create($data);
            $this->logActivity('CREATE', "Menambahkan kategori aset baru: {$this->name}");
            session()->flash('success', "Kategori \"{$this->name}\" berhasil ditambahkan.");
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deletingId   = $id;
        $this->deletingName = $name;
        $this->confirmingDeletion = true;
    }

    public function deleteCategory(): void
    {
        $cat = AssetCategory::findOrFail($this->deletingId);

        if ($cat->assets()->exists()) {
            session()->flash('error', "Tidak bisa menghapus kategori \"{$cat->name}\" karena masih memiliki aset terdaftar.");
        } else {
            $catName = $cat->name;
            $cat->delete();
            $this->logActivity('DELETE', "Menghapus kategori aset: {$catName}");
            session()->flash('success', "Kategori \"{$catName}\" berhasil dihapus.");
        }

        $this->confirmingDeletion = false;
        $this->deletingId         = null;
        $this->deletingName       = '';
    }

    private function resetForm(): void
    {
        $this->parent_id               = null;
        $this->name                    = '';
        $this->description             = '';
        $this->is_active               = true;
        $this->coa_aset_id             = null;
        $this->coa_akum_penyusutan_id  = null;
        $this->coa_beban_penyusutan_id = null;
        $this->coa_kas_id              = null;
        $this->depreciation_method       = '';
        $this->depreciation_rate_annual  = null;
        $this->useful_life_months        = null;
        $this->resetValidation();
    }

    public function render()
    {
        $categories = AssetCategory::with(['parent', 'coaAset', 'coaAkumPenyusutan', 'coaBebanPenyusutan', 'coaKas'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount('assets')
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN id ELSE parent_id END ASC')
            ->orderBy('parent_id', 'ASC')
            ->paginate(15);

        // COA options
        $coaAsetOptions     = Coa::active()->leaf()->where('type', 'ASSET')->orderBy('coa_code')->get();
        $coaExpenseOptions  = Coa::active()->leaf()->where('type', 'EXPENSE')->orderBy('coa_code')->get();
        $coaAllLeaf         = Coa::active()->leaf()->orderBy('coa_code')->get();

        // Root categories for selection
        $rootCategories     = AssetCategory::root()->orderBy('name')->get();

        return view('livewire.assets.categories', [
            'categories'        => $categories,
            'rootCategories'    => $rootCategories,
            'coaAsetOptions'    => $coaAsetOptions,
            'coaExpenseOptions' => $coaExpenseOptions,
            'coaAllLeaf'        => $coaAllLeaf,
        ])->layout('layouts.app');
    }
}
