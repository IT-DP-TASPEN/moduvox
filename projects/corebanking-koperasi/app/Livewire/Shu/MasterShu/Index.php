<?php

namespace App\Livewire\Shu\MasterShu;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MasterShu;
use App\Models\Cif;
use App\Traits\LogsActivity;
use App\Traits\ApprovesActions;

class Index extends Component
{
    use WithPagination, LogsActivity, ApprovesActions;

    public $search = '';

    // Modal state
    public $showCreateModal = false;
    public $showEditModal = false;

    // Form fields
    public $masterShuId;
    public $cif_id;
    public $kriteria;

    // For delete confirmation
    public $confirmingDeletion = false;
    public $deletingId = null;

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Master SHU');
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    // edit method removed from here

    public function save()
    {
        $this->validate([
            'cif_id' => 'required|exists:cifs,id|unique:master_shus,cif_id,' . $this->masterShuId,
            'kriteria' => 'required|in:PEMEGANG SAHAM,PENGAWAS,PENGURUS,ANGGOTA',
            'saving_account_id' => 'nullable|exists:saving_accounts,id',
        ]);

        $data = [
            'cif_id' => $this->cif_id,
            'kriteria' => $this->kriteria,
            'saving_account_id' => $this->saving_account_id ?: null,
        ];

        if ($this->masterShuId) {
            $masterShu = MasterShu::findOrFail($this->masterShuId);
            
            $status = $this->interceptAction('master_shus', 'UPDATE', $data, $masterShu->id, $masterShu->toArray());
            
            if ($status == 'PENDING') {
                $this->resetForm();
                $this->showEditModal = false;
                session()->flash('success', 'Permintaan pembaruan Master SHU telah dikirim ke antrean persetujuan.');
                return;
            }

            $masterShu->update($data);
            $this->logActivity('UPDATE', 'Memperbarui Master SHU CIF: ' . $masterShu->cif->cif_no);
            session()->flash('success', 'Data Master SHU berhasil diperbarui.');
        } else {
            $status = $this->interceptAction('master_shus', 'CREATE', $data);

            if ($status == 'PENDING') {
                $this->resetForm();
                $this->showCreateModal = false;
                session()->flash('success', 'Permintaan pembuatan Master SHU telah dikirim ke antrean persetujuan.');
                return;
            }

            $masterShu = MasterShu::create($data);
            $this->logActivity('CREATE', 'Menambahkan Master SHU baru CIF: ' . $masterShu->cif->cif_no);
            session()->flash('success', 'Data Master SHU berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->showCreateModal = false;
        $this->showEditModal = false;
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        $masterShu = MasterShu::findOrFail($this->deletingId);
        $cifNumber = $masterShu->cif->cif_no;

        $status = $this->interceptAction(
            'master_shus', 
            'DELETE', 
            null,
            $masterShu->id,
            $masterShu->toArray()
        );

        if ($status == 'PENDING') {
            $this->confirmingDeletion = false;
            $this->deletingId = null;
            session()->flash('success', 'Permintaan penghapusan Master SHU telah dikirim ke antrean persetujuan.');
            return;
        }

        $masterShu->delete();

        $this->logActivity('DELETE', 'Menghapus Master SHU CIF: ' . $cifNumber);
        
        $this->confirmingDeletion = false;
        $this->deletingId = null;
        session()->flash('success', 'Data Master SHU berhasil dihapus.');
    }

    public $cifSearch = '';
    public $cifSearchResults = [];
    public $selectedCifName = '';
    public $saving_account_id;
    public $availableSavingAccounts = [];

    public function updatedCifSearch()
    {
        if (strlen($this->cifSearch) >= 3) {
            $this->cifSearchResults = Cif::select('id', 'cif_no', 'name')
                ->where('status', 'ACTIVE')
                ->where(function($q) {
                    $q->where('cif_no', 'like', '%' . $this->cifSearch . '%')
                      ->orWhere('name', 'like', '%' . $this->cifSearch . '%');
                })->take(10)->get();
        } else {
            $this->cifSearchResults = [];
        }
    }

    public function selectCif($id, $cif_no, $name)
    {
        $this->cif_id = $id;
        $this->selectedCifName = $cif_no . ' - ' . $name;
        $this->cifSearch = '';
        $this->cifSearchResults = [];
        
        // Fetch saving accounts for the selected CIF
        $this->availableSavingAccounts = \App\Models\SavingAccount::where('cif_id', $id)
            ->where('status', 'ACTIVE') // Assuming status is ACTIVE
            ->get();
        
        $this->saving_account_id = null;
    }

    public function resetForm()
    {
        $this->reset(['masterShuId', 'cif_id', 'kriteria', 'cifSearch', 'cifSearchResults', 'selectedCifName', 'saving_account_id', 'availableSavingAccounts']);
        $this->resetValidation();
    }

    public function edit($id)
    {
        $this->resetForm();
        $masterShu = MasterShu::with('cif')->findOrFail($id);
        $this->masterShuId = $masterShu->id;
        $this->cif_id = $masterShu->cif_id;
        $this->kriteria = $masterShu->kriteria;
        $this->saving_account_id = $masterShu->saving_account_id;
        $this->selectedCifName = $masterShu->cif->cif_no . ' - ' . $masterShu->cif->name;
        
        $this->availableSavingAccounts = \App\Models\SavingAccount::where('cif_id', $this->cif_id)
            ->where('status', 'ACTIVE')
            ->get();
            
        $this->showEditModal = true;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if (empty($this->search) || strlen($this->search) < 3) {
            $masterShus = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        } else {
            $masterShus = MasterShu::with('cif')
                ->whereHas('cif', function ($query) {
                    $query->where('cif_no', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('kriteria', 'like', '%' . strtoupper($this->search) . '%')
                ->latest()
                ->paginate(10);
        }

        return view('livewire.shu.master-shu.index', [
            'masterShus' => $masterShus,
        ])->layout('layouts.app');
    }
}
