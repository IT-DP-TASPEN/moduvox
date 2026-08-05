<?php

namespace App\Livewire\InsuranceProviders;

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
        $this->logActivity('NAVIGATE', 'Provider Asuransi');
    }

    public $search = '';
    public $showForm = false;
    public $editId = null;

    // Form fields
    public $provider_code, $name, $contact_person, $phone, $email, $address;
    public $is_active = true;

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'contact_person'   => 'nullable|string|max:255',
            'phone'            => 'nullable|string|max:25',
            'email'            => 'nullable|email|max:255',
            'address'          => 'nullable|string',
            'is_active'        => 'boolean',
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
        $provider = InsuranceProvider::findOrFail($id);
        $this->editId = $id;
        $this->name = $provider->name;
        $this->contact_person = $provider->contact_person;
        $this->phone = $provider->phone;
        $this->email = $provider->email;
        $this->address = $provider->address;
        $this->is_active = $provider->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'              => $this->name,
            'contact_person'    => $this->contact_person,
            'phone'             => $this->phone,
            'email'             => $this->email,
            'address'           => $this->address,
            'is_active'         => $this->is_active,
        ];

        if ($this->editId) {
            InsuranceProvider::findOrFail($this->editId)->update(array_merge($data, ['updated_by' => Auth::id()]));
            $this->logActivity('UPDATE', "Memperbarui data provider asuransi: {$this->name}");
            session()->flash('success', 'Data provider berhasil diperbarui.');
        } else {
            $prefix = 'INS-';
            $count = InsuranceProvider::where('provider_code', 'like', $prefix . '%')->count() + 1;
            $data['provider_code'] = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
            $data['created_by'] = Auth::id();
            InsuranceProvider::create($data);
            $this->logActivity('CREATE', "Menambahkan provider asuransi baru: {$this->name}");
            session()->flash('success', 'Provider baru berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'contact_person', 'phone', 'email', 'address', 'editId']);
        $this->is_active = true;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $providers = InsuranceProvider::when(
            $this->search,
            fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('provider_code', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
        )->orderBy('name')->paginate(15);

        return view('livewire.insurance-providers.index', ['providers' => $providers])->layout('layouts.app');
    }
}
