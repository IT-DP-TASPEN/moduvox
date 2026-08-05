<?php

namespace App\Livewire\Loans;

use App\Models\LoanAccount;
use App\Models\LoanTransaction;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use Livewire\Component;
use Livewire\WithPagination;

class Reversal extends Component
{
    use WithPagination, ApprovesActions, LogsActivity;

    public $search = '';
    public $viewMode = 'grid';
    public $selectedLoan = null;
    public $reason = '';

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Reversal Pencairan Pinjaman');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function selectLoan($id)
    {
        $loan = LoanAccount::with(['cif', 'product', 'savingAccount.product', 'transactions'])
            ->findOrFail($id);

        $hasPayment = LoanTransaction::where('loan_account_id', $loan->id)
            ->where('transaction_type', 'like', 'REPAYMENT%')
            ->exists();

        if ($hasPayment) {
            session()->flash('error', 'Pinjaman ini sudah memiliki riwayat angsuran dan tidak dapat diajukan reversal.');
            return;
        }

        $this->selectedLoan = $loan;
        $this->viewMode = 'detail';
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedLoan = null;
        $this->reason = '';
    }

    public function submit()
    {
        $this->validate([
            'reason' => 'required|min:5',
        ]);

        if (!$this->selectedLoan || $this->selectedLoan->status !== 'ACTIVE') {
            session()->flash('error', 'Hanya pinjaman aktif yang dapat diajukan reversal.');
            return;
        }

        $status = $this->interceptAction('loans.reversal', 'Reversal', [
            'loan_account_id' => $this->selectedLoan->id,
            'reason' => $this->reason,
        ], $this->selectedLoan->id);

        if ($status === 'PENDING') {
            $this->logActivity('REVERSE_LOAN_REQUEST', 'Mengajukan reversal pencairan kredit: ' . $this->selectedLoan->account_no);
            session()->flash('success', 'Permintaan reversal pencairan berhasil diajukan ke antrean persetujuan.');
        } else {
            session()->flash('success', 'Reversal pencairan berhasil diproses.');
        }

        return redirect()->route('loans.reversal');
    }

    public function render()
    {
        $query = LoanAccount::with(['cif', 'product'])
            ->where('status', 'ACTIVE')
            ->whereHas('transactions', fn ($trx) => $trx->where('transaction_type', 'DISBURSEMENT'));

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('account_no', 'like', '%' . $this->search . '%')
                    ->orWhere('pk_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cif', function ($qc) {
                        $qc->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('cif_no', 'like', '%' . $this->search . '%');
                    });
            });
            $items = $query->latest()->paginate(10);
        } else {
            $items = LoanAccount::whereRaw('1 = 0')->paginate(10);
        }

        return view('livewire.loans.reversal', [
            'items' => $items,
        ]);
    }
}
