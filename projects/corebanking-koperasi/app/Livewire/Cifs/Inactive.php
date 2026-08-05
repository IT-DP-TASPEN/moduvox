<?php
namespace App\Livewire\Cifs;

use App\Models\Cif;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithLogout;
use App\Traits\ApprovesActions;

use App\Traits\LogsActivity;

class Inactive extends Component
{
    use WithPagination, ApprovesActions, WithLogout, LogsActivity;

    public $search = '';
    
    // View State
    public $viewMode = 'grid'; 
    public $selectedCif = null;
    public $reason = '';

    public function viewCif($id)
    {
        // Only active cif can be closed.
        $this->selectedCif = Cif::with(['branch', 'marketing'])->findOrFail($id);
        $this->viewMode = 'form';
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedCif = null;
        $this->reason = '';
    }

    public function submitInactive()
    {
        $this->validate(['reason' => 'required|string|min:5|max:255']);

        $data = $this->selectedCif->toArray();
        $data['status'] = 'INACTIVE';
        
        unset($data['branch'], $data['marketing']);

        $res = $this->interceptAction('cifs.inactive', 'UPDATE', $data, $this->selectedCif->id, 'CLOSE CIF: ' . $this->reason);
        
        if ($res === 'PENDING') {
            session()->flash('success', 'Instruksi Penutupan CIF telah dikirim ke Daftar Persetujuan.');
            $this->logActivity('INACTIVE_REQUEST', "Mengajukan penonaktifan CIF: " . $this->selectedCif->cif_no . " dengan alasan: " . $this->reason, $this->selectedCif);
        } else {
            session()->flash('success', 'CIF berhasil dinonaktifkan.');
            $this->logActivity('INACTIVE', "Berhasil menonaktifkan CIF: " . $this->selectedCif->cif_no, $this->selectedCif);
        }

        $this->closeView();
    }

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Nonaktifkan CIF');
    }

    public function render()
    {
        $items = collect();
        $query = Cif::with(['branch', 'marketing'])->where('status', 'ACTIVE');
        
        if (!empty(trim($this->search))) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('cif_no', 'like', '%' . $this->search . '%')
                  ->orWhere('nik', 'like', '%' . $this->search . '%');
            });
            $items = $query->latest()->paginate(10);
        } else {
            $items = $query->whereRaw('1 = 0')->paginate(1);
        }

        return view('livewire.cifs.inactive', ['items' => $items])->layout('layouts.app');
    }
}
