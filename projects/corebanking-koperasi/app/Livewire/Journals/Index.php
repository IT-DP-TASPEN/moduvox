<?php

namespace App\Livewire\Journals;

use App\Models\Journal;
use App\Models\Coa;
use App\Models\CoaMovement;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithLogout;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination, WithLogout, ApprovesActions, LogsActivity;

    private const NON_JOURNALABLE_COA_CODES = ['314000'];

    public $viewMode = 'list';
    public $search = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 25;

    // Form fields
    public $transaction_date, $reference_no, $description, $branch_id;
    public $entries = []; // Array of ['coa_id' => '', 'coa_search' => '', 'debit' => 0, 'credit' => 0]
    public $is_revision = false; // Whether this is a revision journal

    public $user, $role;

    protected $queryString = [
        'search' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'perPage' => ['except' => 25],
    ];

    public function mount()
    {
        $this->user = Auth::user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->transaction_date = now()->format('Y-m-d');
        $this->branch_id = $this->user->branch_id;
        $this->addEntry();
        $this->addEntry(); // Start with 2 rows
        $this->logActivity('NAVIGATE', 'Jurnal Umum');
    }

    public function addEntry()
    {
        $this->entries[] = ['coa_id' => '', 'coa_search' => '', 'debit' => 0, 'credit' => 0];
    }

    public function removeEntry($index)
    {
        unset($this->entries[$index]);
        $this->entries = array_values($this->entries);
    }

    protected function generateReferenceNo()
    {
        return 'JM-' . now()->format('YmdHis') . rand(100, 999);
    }

    public function create()
    {
        $this->viewMode = 'form';
    }

    public function updated($property, $value): void
    {
        if (in_array($property, ['search', 'date_from', 'date_to', 'perPage'], true)) {
            $this->resetPage();
        }

        if (preg_match('/^entries\.(\d+)\.coa_search$/', $property, $matches)) {
            $this->resolveEntryCoa((int) $matches[1], $value);
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'date_from', 'date_to']);
        $this->perPage = 25;
        $this->resetPage();
    }

    private function resolveEntryCoa(int $index, mixed $value): void
    {
        $this->entries[$index]['coa_id'] = '';
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
            $this->entries[$index]['coa_id'] = $coa->id;
            $this->entries[$index]['coa_search'] = "{$coa->coa_code} - {$coa->name}";
        }
    }

    public function getTotalDebitProperty()
    {
        return collect($this->entries)->sum(function ($entry) {
            return (float) ($entry['debit'] ?: 0);
        });
    }

    public function getTotalCreditProperty()
    {
        return collect($this->entries)->sum(function ($entry) {
            return (float) ($entry['credit'] ?: 0);
        });
    }

    public function getEntryBalancePreviewsProperty(): array
    {
        $branchId = (int) ($this->branch_id ?: $this->user?->branch_id);
        $date = $this->transaction_date ?: now()->toDateString();
        $coaIds = collect($this->entries)->pluck('coa_id')->filter()->unique()->values();

        if (! $branchId || $coaIds->isEmpty()) {
            return [];
        }

        $coas = Coa::whereIn('id', $coaIds)->get()->keyBy('id');
        $beforeBalances = $coaIds->mapWithKeys(fn ($coaId) => [
            $coaId => $this->coaBalanceBeforeJournal($branchId, (int) $coaId, $date),
        ]);
        $deltas = collect($this->entries)
            ->filter(fn ($entry) => filled($entry['coa_id'] ?? null))
            ->groupBy('coa_id')
            ->map(function ($rows, $coaId) use ($coas) {
                $coa = $coas->get((int) $coaId);
                $debit = $rows->sum(fn ($entry) => (float) ($entry['debit'] ?: 0));
                $credit = $rows->sum(fn ($entry) => (float) ($entry['credit'] ?: 0));

                return $this->normalBalanceDelta($coa?->type, $debit, $credit);
            });

        return collect($this->entries)->mapWithKeys(function ($entry, $index) use ($beforeBalances, $deltas, $coas) {
            $coaId = (int) ($entry['coa_id'] ?? 0);
            if (! $coaId) {
                return [$index => null];
            }

            $before = (float) ($beforeBalances[$coaId] ?? 0);
            $delta = (float) ($deltas[$coaId] ?? 0);
            $coa = $coas->get($coaId);

            return [$index => [
                'before' => $before,
                'after' => $before + $delta,
                'delta' => $delta,
                'normal_side' => in_array($coa?->type, ['ASSET', 'EXPENSE'], true) ? 'Debit' : 'Kredit',
            ]];
        })->all();
    }

    private function coaBalanceBeforeJournal(int $branchId, int $coaId, string $date): float
    {
        return (float) (CoaMovement::query()
            ->where('branch_id', $branchId)
            ->where('coa_id', $coaId)
            ->whereDate('transaction_date', '<=', $date)
            ->orderByDesc('transaction_date')
            ->value('ending_balance') ?? 0);
    }

    private function normalBalanceDelta(?string $coaType, float $debit, float $credit): float
    {
        return in_array($coaType, ['ASSET', 'EXPENSE'], true)
            ? round($debit - $credit, 2)
            : round($credit - $debit, 2);
    }

    public function save()
    {
        foreach ($this->entries as $index => $entry) {
            $this->resolveEntryCoa($index, $entry['coa_search'] ?? '');
        }

        $this->validate([
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:500',
            'entries.*.coa_id' => 'required|exists:coas,id',
            'entries.*.debit' => 'required|numeric|min:0',
            'entries.*.credit' => 'required|numeric|min:0',
            'is_revision' => 'boolean',
        ]);

        if (abs($this->total_debit - $this->total_credit) > 0.001) {
            session()->flash('error', 'Jurnal tidak seimbang! Total Debit (' . number_format($this->total_debit, 2, ',', '.') . ') harus sama dengan Total Kredit (' . number_format($this->total_credit, 2, ',', '.') . ')');
            return;
        }

        if ($this->hasNonJournalableCoa($this->entries)) {
            session()->flash('error', 'COA SHU / LABA TAHUN BERJALAN (314000) dihitung otomatis dari laporan laba rugi dan tidak bisa dijurnal manual.');
            return;
        }

        // Generate automatic reference number
        $referenceNo = $this->generateReferenceNo();

        $entries = collect($this->entries)
            ->map(fn ($entry) => [
                'coa_id' => $entry['coa_id'],
                'debit' => $entry['debit'],
                'credit' => $entry['credit'],
            ])
            ->all();

        $data = [
            'branch_id' => $this->branch_id,
            'transaction_date' => $this->transaction_date,
            'reference_no' => $referenceNo,
            'description' => $this->description,
            'journal_type' => Journal::TYPE_MANUAL,
            'is_revision' => $this->is_revision,
            'entries' => $entries,
            'created_by' => Auth::id()
        ];

        // Route to approval
        $res = $this->interceptAction('journals', 'CREATE', $data, null, null);

        if ($res === 'PENDING') {
            session()->flash('success', 'Jurnal dikirim ke antrean persetujuan.');
            $this->logActivity('CREATE_REQUEST', "Mengajukan penginputan jurnal manual: " . $referenceNo, null, $data);
        } else {
            $this->createApprovedJournal($data);
            session()->flash('success', 'Jurnal berhasil diposting.');
            $this->logActivity('CREATE', "Berhasil memposting jurnal manual: " . $referenceNo, null, $data);
        }

        $this->viewMode = 'list';
        $this->reset(['description', 'is_revision']);
        $this->entries = [];
        $this->addEntry();
        $this->addEntry();
    }

    private function createApprovedJournal(array $data): Journal
    {
        return DB::transaction(function () use ($data) {
            $entries = $data['entries'];
            unset($data['entries']);

            $data['status'] = 'APPROVED';
            $data['approved_by'] = Auth::id();
            $data['approved_at'] = now();

            $journal = Journal::create($data);

            foreach ($entries as $entry) {
                $journal->entries()->create($entry);
            }

            app(\App\Services\CoaMovementService::class)->syncForJournal($journal);

            return $journal;
        });
    }

    private function hasNonJournalableCoa(array $entries): bool
    {
        $coaIds = collect($entries)->pluck('coa_id')->filter()->all();

        if (empty($coaIds)) {
            return false;
        }

        return Coa::whereIn('id', $coaIds)
            ->whereIn('coa_code', self::NON_JOURNALABLE_COA_CODES)
            ->exists();
    }

    public function render()
    {
        $query = Journal::with(['branch', 'entries.coa']);

        $search = trim((string) $this->search);

        $query
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('entries.coa', function ($coaQuery) use ($search) {
                            $coaQuery->where('coa_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('entries', function ($entryQuery) use ($search) {
                            $entryQuery->where('reference_no', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
                        });
                });
            })
            ->when($this->date_from, fn ($query) => $query->whereDate('transaction_date', '>=', $this->date_from))
            ->when($this->date_to, fn ($query) => $query->whereDate('transaction_date', '<=', $this->date_to));

        return view('livewire.journals.index', [
            'journals' => $query
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->paginate((int) $this->perPage),
            'coas' => Coa::where('is_leaf', true)
                ->where('is_active', true)
                ->whereNotIn('coa_code', self::NON_JOURNALABLE_COA_CODES)
                ->orderBy('coa_code')
                ->get(),
            'branches' => Branch::all()
        ])->layout('layouts.app');
    }
}
