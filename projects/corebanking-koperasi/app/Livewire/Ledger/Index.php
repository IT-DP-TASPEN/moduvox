<?php

namespace App\Livewire\Ledger;

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\Branch;
use Livewire\Component;
use Livewire\Attributes\Url;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;

class Index extends Component
{
    use WithLogout, LogsActivity;

    #[Url]
    public $filter_coa;

    public $coaSearch = '';

    #[Url]
    public $filter_branch;

    #[Url]
    public $date_from;

    #[Url]
    public $date_to;

    public $user, $role;

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->date_from = $this->date_from ?: now()->startOfMonth()->format('Y-m-d');
        $this->date_to = $this->date_to ?: now()->endOfMonth()->format('Y-m-d');
        $this->filter_branch = $this->filter_branch ?? $this->user->branch_id;
        $this->syncCoaSearch();
        $this->logActivity('NAVIGATE', 'Buku Besar');
    }

    public function updatedCoaSearch($value): void
    {
        $this->filter_coa = null;
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $code = str_contains($value, ' - ') ? trim(explode(' - ', $value, 2)[0]) : $value;
        $coa = Coa::active()
            ->leaf()
            ->where(function ($query) use ($value, $code) {
                $query->where('coa_code', $code)
                    ->orWhere('coa_code', $value)
                    ->orWhereRaw("CONCAT(coa_code, ' - ', name) = ?", [$value]);
            })
            ->first();

        if ($coa) {
            $this->filter_coa = $coa->id;
            $this->coaSearch = "{$coa->coa_code} - {$coa->name}";
        }
    }

    public function updatedFilterCoa(): void
    {
        $this->syncCoaSearch();
    }

    private function syncCoaSearch(): void
    {
        $coa = $this->filter_coa ? Coa::find($this->filter_coa) : null;
        $this->coaSearch = $coa ? "{$coa->coa_code} - {$coa->name}" : '';
    }

    public function downloadExport()
    {
        $this->logActivity('EXPORT', 'Export Buku Besar');

        $filename = 'Buku_Besar_' . ($this->date_from ?: 'all') . '_' . ($this->date_to ?: 'all') . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['COA', 'Nama Akun', 'Tanggal', 'Referensi', 'Keterangan', 'Debit', 'Kredit', 'Saldo']);

            $coas = Coa::active()
                ->leaf()
                ->when($this->filter_coa, fn ($query) => $query->whereKey($this->filter_coa))
                ->orderBy('coa_code')
                ->get();

            foreach ($coas as $coa) {
                $openingBalance = $this->openingBalance($coa);
                $runningBalance = $openingBalance;

                fputcsv($handle, [
                    $coa->coa_code,
                    $coa->name,
                    $this->date_from ?: '-',
                    '',
                    'Saldo Awal',
                    0,
                    0,
                    $openingBalance,
                ]);

                foreach ($this->ledgerEntries($coa->id) as $entry) {
                    $runningBalance += $this->signedMutation($coa, (float) $entry->debit, (float) $entry->credit);

                    fputcsv($handle, [
                        $coa->coa_code,
                        $coa->name,
                        $entry->journal->transaction_date->format('Y-m-d'),
                        $entry->reference_no ?: $entry->journal->reference_no,
                        $entry->description ?: $entry->journal->description,
                        (float) $entry->debit,
                        (float) $entry->credit,
                        $runningBalance,
                    ]);
                }

                fputcsv($handle, [
                    $coa->coa_code,
                    $coa->name,
                    $this->date_to ?: '-',
                    '',
                    'Saldo Akhir',
                    '',
                    '',
                    $runningBalance,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function openingBalance(Coa $coa): float
    {
        $debit = JournalEntry::where('coa_id', $coa->id)
            ->whereHas('journal', function ($query) {
                $query->whereDate('transaction_date', '<', $this->date_from)
                    ->where('status', 'APPROVED');
                if ($this->filter_branch) $query->where('branch_id', $this->filter_branch);
            })
            ->sum('debit');

        $credit = JournalEntry::where('coa_id', $coa->id)
            ->whereHas('journal', function ($query) {
                $query->whereDate('transaction_date', '<', $this->date_from)
                    ->where('status', 'APPROVED');
                if ($this->filter_branch) $query->where('branch_id', $this->filter_branch);
            })
            ->sum('credit');

        return $this->signedMutation($coa, (float) $debit, (float) $credit);
    }

    private function ledgerEntries(int $coaId)
    {
        return JournalEntry::with('journal')
            ->where('coa_id', $coaId)
            ->whereHas('journal', function ($query) {
                $query->whereDate('transaction_date', '>=', $this->date_from)
                    ->whereDate('transaction_date', '<=', $this->date_to)
                    ->where('status', 'APPROVED');
                if ($this->filter_branch) $query->where('branch_id', $this->filter_branch);
            })
            ->get()
            ->sortBy(fn ($entry) => $entry->journal->transaction_date->format('Y-m-d') . '-' . str_pad((string) $entry->id, 12, '0', STR_PAD_LEFT));
    }

    private function signedMutation(Coa $coa, float $debit, float $credit): float
    {
        return in_array($coa->type, ['ASSET', 'EXPENSE'], true)
            ? $debit - $credit
            : $credit - $debit;
    }

    public function render()
    {
        $entries = collect();
        $openingBalance = 0;

        if ($this->filter_coa) {
            $coa = Coa::findOrFail($this->filter_coa);
            $openingBalance = $this->openingBalance($coa);
            $entries = $this->ledgerEntries((int) $this->filter_coa);
        }

        return view('livewire.ledger.index', [
            'entries' => $entries,
            'openingBalance' => $openingBalance,
            'coas' => Coa::active()->leaf()->orderBy('coa_code')->get(),
            'branches' => Branch::all()
        ])->layout('layouts.app');
    }
}
