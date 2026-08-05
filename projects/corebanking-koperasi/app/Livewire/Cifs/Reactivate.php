<?php
namespace App\Livewire\Cifs;

use App\Models\Cif;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithLogout;
use App\Traits\ApprovesActions;

use App\Traits\LogsActivity;

class Reactivate extends Component
{
    use WithPagination, ApprovesActions, WithLogout, LogsActivity;

    public $search = '';
    
    // View State
    public $viewMode = 'grid'; 
    public $selectedCif = null;
    public $reason = '';

    public function viewCif($id)
    {
        $this->selectedCif = Cif::with(['branch', 'marketing'])->findOrFail($id);
        $this->viewMode = 'form';
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedCif = null;
        $this->reason = '';
    }

    public function submitReactive()
    {
        $this->validate(['reason' => 'required|string|min:5|max:255']);

        $data = $this->selectedCif->toArray();
        $data['status'] = 'ACTIVE';
        
        unset($data['branch'], $data['marketing']);

        $res = $this->interceptAction('cifs.reactivate', 'UPDATE', $data, $this->selectedCif->id, 'REACTIVATE CIF: ' . $this->reason);
        
        if ($res === 'PENDING') {
            session()->flash('success', 'Instruksi Pengaktifan Kembali CIF telah dikirim ke Daftar Persetujuan.');
            $this->logActivity('REACTIVE_REQUEST', "Mengajukan reaktivasi CIF: " . $this->selectedCif->cif_no . " dengan alasan: " . $this->reason, $this->selectedCif);
        } else {
            session()->flash('success', 'CIF berhasil dipulihkan dan diaktifkan.');
            $this->logActivity('REACTIVE', "Berhasil mereaktivasi CIF: " . $this->selectedCif->cif_no, $this->selectedCif);
        }

        $this->closeView();
    }

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Reaktivasi CIF');
    }

    public function render()
    {
        $items = collect();
        $query = Cif::with(['branch', 'marketing'])->whereIn('status', ['INACTIVE', 'BLOCKED']);
        
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

        return view('livewire.cifs.reactivate', ['items' => $items])->layout('layouts.app');
    }
}
