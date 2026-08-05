<?php

namespace App\Livewire\Loans;

use App\Models\LoanAccount;
use App\Services\LoanOperationService;
use App\Services\SettlementEngine;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Traits\ApprovesActions;
use Livewire\WithPagination;
use App\Traits\LogsActivity;

class Disbursement extends Component
{
    use ApprovesActions, WithPagination, LogsActivity;

    public $search = '';
    public $statusFilter = '';
    public int $perPage = 12;
    public $confirmingDisbursementId = null;
    public $disbursement_channel = 'INTERNAL'; // INTERNAL | CASH | ABA
    public $bank_coa_id = null;
    public $cash_coa_id = null;

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Pencairan Dana');
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDisbursementChannel($channel)
    {
        $this->bank_coa_id = null;
        $this->cash_coa_id = null;

        $options = SettlementEngine::getSelectableCoas($channel);
        if ($options->count() === 1) {
            if ($channel === 'ABA') {
                $this->bank_coa_id = $options->first()->id;
            } else if ($channel === 'CASH') {
                $this->cash_coa_id = $options->first()->id;
            }
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // View Management
    public $viewMode = 'grid'; // grid or detail
    public $selectedAccount = null;

    public function viewAccount($id)
    {
        $this->selectedAccount = LoanAccount::with(['cif', 'product', 'savingAccount', 'schedules', 'transactions'])
            ->findOrFail($id);
        $this->viewMode = 'detail';
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedAccount = null;
    }

    public function render()
    {
        $query = LoanAccount::with(['cif', 'product'])
            ->where('status', 'APPROVED')
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('account_no', 'like', '%' . $this->search . '%')
                        ->orWhere('pk_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('cif', function ($qCif) {
                            $qCif->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy('updated_at', 'desc');

        $loans = ($this->search || $this->statusFilter)
            ? $query->paginate($this->perPage)
            : LoanAccount::whereRaw('1 = 0')->paginate($this->perPage);

        $abaCoas  = SettlementEngine::getSelectableCoas('ABA');
        $cashCoas = SettlementEngine::getSelectableCoas('CASH');

        return view('livewire.loans.disbursement', [
            'loans' => $loans,
            'abaCoas' => $abaCoas,
            'cashCoas' => $cashCoas,
        ]);
    }

    public function confirmDisbursement($id)
    {
        $loan = LoanAccount::find($id);
        if ($loan && $loan->status === 'APPROVED') {
            $this->confirmingDisbursementId = $id;
        }
    }

    public function processDisbursement(LoanOperationService $service)
    {
        if (!$this->confirmingDisbursementId) return;

        $loan = LoanAccount::find($this->confirmingDisbursementId);
        if (!$loan || $loan->status !== 'APPROVED') return;

        // Validate channel requirements
        if ($this->disbursement_channel === 'INTERNAL' && !$loan->saving_account_id) {
            session()->flash('error', 'Pencairan INTERNAL memerlukan rekening tabungan nasabah. Silakan daftar rekening tabungan terlebih dahulu.');
            return;
        }

        try {
            $coaOverrideId = ($this->disbursement_channel === 'ABA')
                ? ($this->bank_coa_id ?: null)
                : ($this->cash_coa_id ?: null);

            $status = $this->interceptAction('loans.disbursement', 'Disbursement', [
                'loan_account_id' => $loan->id,
                'channel' => $this->disbursement_channel,
                'coa_override_id' => $coaOverrideId,
            ], $loan->id);

            if ($status === 'PENDING') {
                $this->logActivity('DISBURSE_LOAN_REQUEST', 'Mengajukan pencairan Dana Pinjaman: ' . $loan->account_no . ' via ' . $this->disbursement_channel);
                session()->flash('success', 'Permintaan pencairan dana berhasil diajukan ke antrean persetujuan.');
            } else {
                DB::transaction(function () use ($service, $loan, $coaOverrideId) {
                    $service->disburseLoan($loan, $this->disbursement_channel, $coaOverrideId);
                });
                $this->logActivity('DISBURSE_LOAN', 'Pencairan Dana Pinjaman: ' . $loan->account_no . ' via ' . $this->disbursement_channel);
                session()->flash('success', 'Pencairan dana berhasil dilakukan. Jadwal angsuran dan jurnal otomatis terbentuk.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal melakukan pencairan: ' . $e->getMessage());
        }

        $this->confirmingDisbursementId = null;
    }
}
