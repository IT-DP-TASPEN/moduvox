<?php
namespace App\Livewire\Cifs;

use App\Models\Cif;
use App\Models\Branch;
use App\Models\MarketingMaster;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithLogout;
use App\Traits\ApprovesActions;

use App\Traits\LogsActivity;

class Mutation extends Component
{
    use WithPagination, ApprovesActions, WithLogout, LogsActivity;

    public $search = '';
    public $filter_branch = '';
    
    // View State
    public $viewMode = 'grid'; 
    public $selectedCif = null;

    // Mutated Fields
    public $branch_id;
    public $marketing_id;

    public function viewCif($id)
    {
        $this->selectedCif = Cif::with(['branch', 'marketing'])->findOrFail($id);
        $this->branch_id = $this->selectedCif->branch_id;
        $this->marketing_id = $this->selectedCif->marketing_id;
        $this->viewMode = 'form';
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedCif = null;
    }

    public function submitMutation()
    {
        $this->validate([
            'branch_id' => 'required|exists:branches,id',
            'marketing_id' => 'nullable|exists:marketing_masters,id'
        ]);

        $data = $this->selectedCif->toArray();
        $originalBranch = $data['branch_id'];

        $data['branch_id'] = $this->branch_id;
        $data['marketing_id'] = $this->marketing_id;
        // Strip relations out before caching to JSON
        unset($data['branch'], $data['marketing']);

        // Check if there was actually a change
        if ($this->branch_id == $originalBranch && $this->marketing_id == $this->selectedCif->marketing_id) {
            session()->flash('error', 'Tidak ada perubahan cabang asal/tujuan.');
            return;
        }

        $res = $this->interceptAction('cifs.mutation', 'MUTATION', $data, $this->selectedCif->id, 'MUTATION: Pemindahan Cabang Nasional');
        
        if ($res === 'PENDING') {
            session()->flash('success', 'Permintaan Mutasi telah masuk Antrean.');
            $this->logActivity('MUTATION_REQUEST', "Mengajukan mutasi cabang untuk CIF: " . $this->selectedCif->cif_no, $this->selectedCif, ['to_branch_id' => $this->branch_id]);
        } else {
            session()->flash('success', 'Data Mutasi berhasil disahkan.');
            $this->logActivity('MUTATION', "Berhasil melakukan mutasi cabang untuk CIF: " . $this->selectedCif->cif_no, $this->selectedCif, ['to_branch_id' => $this->branch_id]);
        }

        $this->closeView();
    }

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Mutasi Cabang CIF');
    }

    public function render()
    {
        $items = collect();
        $query = Cif::with(['branch', 'marketing'])->where('status', 'ACTIVE');
        
        if (!empty(trim($this->search)) || !empty($this->filter_branch)) {
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('cif_no', 'like', '%' . $this->search . '%')
                      ->orWhere('nik', 'like', '%' . $this->search . '%');
                });
            }
            if ($this->filter_branch) {
                $query->where('branch_id', $this->filter_branch);
            }
            $items = $query->latest()->paginate(10);
        } else {
            $items = $query->whereRaw('1 = 0')->paginate(1);
        }

        return view('livewire.cifs.mutation', [
            'items' => $items,
            'branches' => Branch::where('is_active', true)->get(),
            'marketings' => MarketingMaster::where('is_active', true)->get()
        ])->layout('layouts.app');
    }
}
