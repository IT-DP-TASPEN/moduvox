<?php

namespace App\Livewire\InsuranceProducts;

use App\Models\InsuranceProduct;
use App\Models\InsuranceProvider;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\LogsActivity;

class Index extends Component
{
    use WithPagination, LogsActivity;

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Produk Asuransi');
    }

    public $search = '';
    public $showForm = false;
    public $editId = null;

    // Form fields
    public $insurance_provider_id, $product_code, $name, $type = 'JIWA', $calculation_base = 'PLAFOND';
    public $is_active = true;

    protected function rules(): array
    {
        return [
            'insurance_provider_id' => 'required|exists:insurance_providers,id',
            'name'                  => 'required|string|max:255',
            'type'                  => 'required|in:JIWA,KENDARAAN,BANGUNAN,KREDIT',
            'calculation_base'      => 'required|in:PLAFOND,OUSTANDING',
            'is_active'             => 'boolean',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editId = null;
    }

    public function openEdit(int $id): void
    {
        $product = InsuranceProduct::findOrFail($id);
        $this->editId = $id;
        $this->insurance_provider_id = $product->insurance_provider_id;
        $this->name = $product->name;
        $this->type = $product->type;
        $this->calculation_base = $product->calculation_base;
        $this->is_active = $product->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'insurance_provider_id' => $this->insurance_provider_id,
            'name'                  => $this->name,
            'type'                  => $this->type,
            'calculation_base'      => $this->calculation_base,
            'is_active'             => $this->is_active,
        ];

        if ($this->editId) {
            $product = InsuranceProduct::findOrFail($this->editId);
            $product->update(array_merge($data, ['updated_by' => Auth::id()]));

            $this->logActivity('UPDATE', "Memperbarui data produk asuransi: {$this->name}");
            session()->flash('success', 'Data produk asuransi berhasil diperbarui.');
        } else {
            $prefix = 'INP-';
            $count = InsuranceProduct::where('product_code', 'like', $prefix . '%')->count() + 1;
            $data['product_code'] = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
            $data['created_by'] = Auth::id();
            $product = InsuranceProduct::create($data);

            $this->logActivity('CREATE', "Menambahkan produk asuransi baru: {$this->name}");
            session()->flash('success', 'Produk asuransi baru berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function resetForm(): void
    {
        $this->reset(['insurance_provider_id', 'name', 'type', 'calculation_base', 'editId']);
        $this->type = 'JIWA';
        $this->calculation_base = 'PLAFOND';
        $this->is_active = true;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = InsuranceProduct::with('provider')->when(
            $this->search,
            fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('product_code', 'like', "%{$this->search}%")
                ->orWhereHas('provider', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
        )->orderBy('name')->paginate(15);

        return view('livewire.insurance-products.index', [
            'products' => $products,
            'providers' => InsuranceProvider::where('is_active', true)->get()
        ])->layout('layouts.app');
    }
}
