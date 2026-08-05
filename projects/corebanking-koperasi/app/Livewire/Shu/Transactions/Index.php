<?php

namespace App\Livewire\Shu\Transactions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ShuDistribution;
use App\Models\ShuDistributionDetail;
use App\Models\MasterShu;
use App\Models\SavingTransaction;
use App\Traits\LogsActivity;
use App\Traits\ApprovesActions;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination, LogsActivity, ApprovesActions;

    // Inputs
    public $periode;
    public $total_laba;
    
    // Percentages
    public $persen_saham = 20;
    public $persen_pengawas = 30;
    public $persen_pengurus = 40;
    public $persen_anggota = 10;

    // Calculation Results
    public $preview = [];
    public $totalCalculatedLaba = 0;
    public $totalOrang = 0;
    
    // View state
    public $activeTab = 'calculator'; // calculator, history
    
    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Transaksi SHU');
    }

    public function calculate()
    {
        $this->validate([
            'periode' => 'required|string|max:255',
            'total_laba' => 'required|numeric|min:1',
            'persen_saham' => 'required|numeric|min:0|max:100',
            'persen_pengawas' => 'required|numeric|min:0|max:100',
            'persen_pengurus' => 'required|numeric|min:0|max:100',
            'persen_anggota' => 'required|numeric|min:0|max:100',
        ]);

        $totalPersen = $this->persen_saham + $this->persen_pengawas + $this->persen_pengurus + $this->persen_anggota;
        if ($totalPersen != 100) {
            session()->flash('error', 'Total persentase harus tepat 100%. Saat ini: ' . $totalPersen . '%');
            return;
        }

        // Hitung semua anggota per kriteria dari Master SHU (tanpa filter rekening)
        $counts = MasterShu::select('kriteria', DB::raw('count(*) as total'))
            ->groupBy('kriteria')
            ->pluck('total', 'kriteria');

        $kriteriaList = [
            'PEMEGANG SAHAM' => $this->persen_saham,
            'PENGAWAS'       => $this->persen_pengawas,
            'PENGURUS'       => $this->persen_pengurus,
            'ANGGOTA'        => $this->persen_anggota,
        ];

        $this->preview = [];
        $this->totalCalculatedLaba = 0;
        $this->totalOrang = 0;
        
        $totalLabaNumeric = (float) $this->total_laba;

        foreach ($kriteriaList as $nama => $persen) {
            $jumlahOrang = $counts[$nama] ?? 0;
            $shuKriteria = ($totalLabaNumeric * $persen) / 100;
            $perOrang = $jumlahOrang > 0 ? ($shuKriteria / $jumlahOrang) : 0;

            $this->preview[] = [
                'kriteria' => $nama,
                'persentase' => $persen,
                'shu' => $shuKriteria,
                'jumlah_orang' => $jumlahOrang,
                'per_orang' => $perOrang,
            ];

            $this->totalCalculatedLaba += $shuKriteria;
            $this->totalOrang += $jumlahOrang;
        }
    }

    public function distribute()
    {
        if (empty($this->preview)) {
            session()->flash('error', 'Silakan hitung SHU terlebih dahulu.');
            return;
        }

        $totalLabaNumeric = (float) $this->total_laba;

        $data = [
            'periode'  => $this->periode,
            'total_laba' => $totalLabaNumeric,
            'details' => $this->preview
        ];

        // Intercept with correct module_key = 'shu.distributions', action = 'DISTRIBUTE'
        $status = $this->interceptAction('shu.distributions', 'DISTRIBUTE', $data);

        if ($status == 'PENDING') {
            session()->flash('success', 'Permintaan distribusi SHU (' . $this->periode . ') telah dikirim ke antrean persetujuan.');
            $this->reset(['periode', 'total_laba', 'preview', 'persen_saham', 'persen_pengawas', 'persen_pengurus', 'persen_anggota']);
            $this->persen_saham = 20; $this->persen_pengawas = 30; $this->persen_pengurus = 40; $this->persen_anggota = 10;
            $this->activeTab = 'history';
            return;
        }

        $this->executeDistribution($data);
    }

    public function executeDistribution($data)
    {
        try {
            $service = app(\App\Services\ShuOperationService::class);
            $service->executeDistribution($data);

            $this->logActivity('CREATE', 'Distribusi SHU periode: ' . $data['periode']);
            session()->flash('success', 'Distribusi SHU periode ' . $data['periode'] . ' berhasil dilaksanakan.');
            $this->reset(['periode', 'total_laba', 'preview']);
            $this->persen_saham = 20; $this->persen_pengawas = 30; $this->persen_pengurus = 40; $this->persen_anggota = 10;
            $this->activeTab = 'history';

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membagikan SHU: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $histories = ShuDistribution::latest()->paginate(10);
        return view('livewire.shu.transactions.index', [
            'histories' => $histories
        ])->layout('layouts.app');
    }
}
