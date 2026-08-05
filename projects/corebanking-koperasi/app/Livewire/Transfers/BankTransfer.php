<?php

namespace App\Livewire\Transfers;

use App\Models\Coa;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use App\Traits\ApprovesActions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BankTransfer extends Component
{
    use LogsActivity, WithLogout, ApprovesActions;

    // Form fields
    public $transfer_date;
    public $from_coa_id = '';
    public $to_coa_id = '';
    public $amount = '';
    public $description = '';
    public $reference_no = '';

    // UI state
    public $recentTransfers = [];

    public function mount()
    {
        $this->transfer_date = now()->format('Y-m-d');
        $this->reference_no  = 'ATB-' . now()->format('YmdHis');
        $this->logActivity('NAVIGATE', 'Transaksi Antar Bank');
        $this->recentTransfers = $this->getRecentTransfers();
    }

    public function rules(): array
    {
        return [
            'transfer_date' => 'required|date',
            'from_coa_id'   => 'required|exists:coas,id',
            'to_coa_id'     => 'required|exists:coas,id|different:from_coa_id',
            'amount'        => 'required|numeric|min:1',
            'description'   => 'required|string|max:500',
            'reference_no'  => 'required|string|max:100',
        ];
    }

    public function save()
    {
        $this->validate();

        $amount = (float) str_replace(['.', ','], ['', '.'], $this->amount);

        $data = [
            'transfer_date' => $this->transfer_date,
            'from_coa_id'   => $this->from_coa_id,
            'to_coa_id'     => $this->to_coa_id,
            'amount'        => $amount,
            'description'   => $this->description,
            'reference_no'  => $this->reference_no,
            'branch_id'     => Auth::user()->branch_id,
        ];

        $status = $this->interceptAction('transfers.bank', 'CREATE', $data);

        $this->logActivity('BANK_TRANSFER_REQUEST', "Transaksi Antar Bank: {$this->reference_no} sejumlah Rp " . number_format($amount, 0, ',', '.'));

        if ($status === 'PENDING') {
            session()->flash('success', 'Transaksi Antar Bank telah diajukan ke antrean persetujuan.');
        } else {
            $this->executeTransfer($data);
            session()->flash('success', 'Transaksi Antar Bank berhasil dicatat.');
        }

        $this->resetForm();
        $this->recentTransfers = $this->getRecentTransfers();
    }

    protected function executeTransfer(array $data): Journal
    {
        return DB::transaction(function () use ($data) {
            $journal = Journal::create([
                'branch_id'        => $data['branch_id'],
                'transaction_date' => $data['transfer_date'],
                'reference_no'     => $data['reference_no'],
                'description'      => "Transaksi Antar Bank: {$data['description']}",
                'status'           => 'APPROVED',
                'created_by'       => Auth::id(),
                'approved_by'      => Auth::id(),
                'approved_at'      => now(),
            ]);

            // Dr destination bank (incoming)
            JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id'     => $data['to_coa_id'],
                'debit'      => $data['amount'],
                'credit'     => 0,
            ]);

            // Cr source bank (outgoing)
            JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id'     => $data['from_coa_id'],
                'debit'      => 0,
                'credit'     => $data['amount'],
            ]);

            return $journal;
        });
    }

    protected function resetForm(): void
    {
        $this->amount        = '';
        $this->description   = '';
        $this->from_coa_id   = '';
        $this->to_coa_id     = '';
        $this->transfer_date = now()->format('Y-m-d');
        $this->reference_no  = 'ATB-' . now()->format('YmdHis');
    }

    protected function getRecentTransfers(): array
    {
        return Journal::with(['entries.coa'])
            ->where('reference_no', 'like', 'ATB-%')
            ->orderByDesc('transaction_date')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function render()
    {
        // COA: hanya yang merupakan rekening kas/bank aktif (is_leaf=true, is_cash=true)
        $bankCoas = Coa::where('is_active', true)
            ->where('is_leaf', true)
            ->where(function ($q) {
                $q->where('is_cash', true)
                  ->orWhere('coa_code', 'like', '1-112%');
            })
            ->orderBy('coa_code')
            ->get();

        return view('livewire.transfers.bank-transfer', [
            'bankCoas' => $bankCoas,
        ])->layout('layouts.app');
    }
}
