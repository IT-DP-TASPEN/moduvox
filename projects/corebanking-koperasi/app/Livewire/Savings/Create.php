<?php

namespace App\Livewire\Savings;

use App\Models\Cif;
use App\Models\SavingProduct;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use ApprovesActions, LogsActivity, WithLogout;

    public $cif_id;
    public $saving_product_id;
    public $initial_deposit = 0;
    public $note = '';

    public $searchCif = '';
    public $selectedCif = null;

    protected $rules = [
        'cif_id' => 'required|exists:cifs,id',
        'saving_product_id' => 'required|exists:saving_products,id',
    ];

    public function updatedSearchCif()
    {
        $this->selectedCif = null;
    }

    public function selectCif($id)
    {
        $this->selectedCif = Cif::find($id);
        $this->cif_id = $id;
        $this->searchCif = $this->selectedCif->name . ' (' . $this->selectedCif->cif_no . ')';
    }

    public function save()
    {
        $this->validate();

        $product = SavingProduct::find($this->saving_product_id);

        $data = [
            'cif_id' => $this->cif_id,
            'saving_product_id' => $this->saving_product_id,
            'branch_id' => Auth::user()->branch_id,
            'initial_deposit' => 0,
            'note' => $this->note,
        ];

        $status = $this->interceptAction('savings.create', 'CREATE', $data);

        $this->logActivity('CREATE_SAVING_REQUEST', "Mengajukan pembukaan rekening [{$product->name}] untuk Anggota [{$this->selectedCif->name}]");

        if ($status === 'PENDING') {
            session()->flash('success', 'Permohonan pembukaan rekening telah diajukan ke antrean persetujuan.');
        } else {
            // Auto Approval: Langsung eksekusi operasi pembukaan rekening
            $service = new \App\Services\SavingOperationService();
            $service->openAccount($data);
            
            session()->flash('success', 'Rekening berhasil dibuka.');
        }

        return redirect()->route('savings.inquiry');
    }

    public function mount()
    {
        $cifId = request()->query('cif');
        if ($cifId) {
            $this->selectCif($cifId);
        }
        $this->logActivity('NAVIGATE', 'Buka Rekening Baru');
    }

    public function render()
    {
        $query = Cif::query();

        if (strlen($this->searchCif) >= 3 && !$this->selectedCif) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchCif . '%')
                  ->orWhere('cif_no', 'like', '%' . $this->searchCif . '%')
                  ->orWhere('nik', 'like', '%' . $this->searchCif . '%');
            });
            $cifs = $query->limit(5)->get();
        } else {
            $cifs = [];
        }

        return view('livewire.savings.create', [
            'cifResults' => $cifs,
            'products' => SavingProduct::where('is_active', true)->get(),
            'user' => Auth::user(),
            'role' => Auth::user()->getRoleNames()->first()
        ])->layout('layouts.app');
    }
}
