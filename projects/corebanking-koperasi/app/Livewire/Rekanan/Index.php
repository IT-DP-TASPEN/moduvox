<?php

namespace App\Livewire\Rekanan;

use App\Models\Rekanan as RekananModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\LogsActivity;

class Index extends Component
{
    use WithPagination, LogsActivity;

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Master Rekanan');
    }

    public $search = '';
    public $showForm = false;
    public $editId = null;

    // Form fields
    public $name, $contact_person, $phone, $email;
    public $address, $npwp, $bank_name, $bank_account_no, $bank_account_name;
    public $is_active = true;

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'contact_person'   => 'nullable|string|max:255',
            'phone'            => 'nullable|string|max:25',
            'email'            => 'nullable|email|max:255',
            'address'          => 'nullable|string',
            'npwp'             => 'nullable|string|max:30',
            'bank_name'        => 'nullable|string|max:100',
            'bank_account_no'  => 'nullable|string|max:50',
            'bank_account_name'=> 'nullable|string|max:255',
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
        $rekanan = RekananModel::findOrFail($id);
        $this->editId = $id;
        $this->name = $rekanan->name;
        $this->contact_person = $rekanan->contact_person;
        $this->phone = $rekanan->phone;
        $this->email = $rekanan->email;
        $this->address = $rekanan->address;
        $this->npwp = $rekanan->npwp;
        $this->bank_name = $rekanan->bank_name;
        $this->bank_account_no = $rekanan->bank_account_no;
        $this->bank_account_name = $rekanan->bank_account_name;
        $this->is_active = $rekanan->is_active;
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
            'npwp'              => $this->npwp,
            'bank_name'         => $this->bank_name,
            'bank_account_no'   => $this->bank_account_no,
            'bank_account_name' => $this->bank_account_name,
            'is_active'         => $this->is_active,
        ];

        if ($this->editId) {
            RekananModel::findOrFail($this->editId)->update(array_merge($data, ['updated_by' => Auth::id()]));
            $this->logActivity('UPDATE', "Memperbarui data rekanan: {$this->name}");
            session()->flash('success', 'Data rekanan berhasil diperbarui.');
        } else {
            $prefix = 'RKN-';
            $count = RekananModel::where('rekanan_code', 'like', $prefix . '%')->count() + 1;
            $data['rekanan_code'] = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
            $data['created_by'] = Auth::id();
            RekananModel::create($data);
            $this->logActivity('CREATE', "Menambahkan rekanan baru: {$this->name}");
            session()->flash('success', 'Rekanan baru berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'contact_person', 'phone', 'email', 'address', 'npwp',
                      'bank_name', 'bank_account_no', 'bank_account_name', 'editId']);
        $this->is_active = true;
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function render()
    {
        $rekanan = RekananModel::when($this->search, fn($q) => $q
            ->where('name', 'like', "%{$this->search}%")
            ->orWhere('rekanan_code', 'like', "%{$this->search}%")
            ->orWhere('phone', 'like', "%{$this->search}%")
        )->orderBy('name')->paginate(15);

        return view('livewire.rekanan.index', ['rekanan' => $rekanan])->layout('layouts.app');
    }
}
