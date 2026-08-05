<?php

namespace App\Livewire\TrialBalance;

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\Branch;
use App\Models\TaxSetting;
use Livewire\Component;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithLogout, LogsActivity;

    private const CURRENT_YEAR_PROFIT_COA = '314000';
    private const LAST_YEAR_PROFIT_COA = '315000';

    public $date_from;
    public $date_to;
    public $filter_branch;
    public $search = '';

    public $user, $role;

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
        $this->filter_branch = ''; // Default to All Branches
        $this->logActivity('NAVIGATE', 'Neraca Saldo');
    }

    public function render()
    {
        return view('livewire.trial-balance.index', [
            'groupedCoas' => $this->groupedCoas(),
            'branches' => Branch::all(),
            'profitLossSummary' => $this->profitLossSummary(),
        ])->layout('layouts.app');
    }

    public function downloadExport()
    {
        $this->logActivity('EXPORT', 'Export Neraca Saldo');

        $filename = 'Neraca_Saldo_' . ($this->date_from ?: 'all') . '_' . ($this->date_to ?: 'all') . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Tipe', 'Saldo Awal', 'Debit', 'Kredit', 'Saldo Akhir']);

            foreach ($this->groupedCoas() as $type => $coas) {
                fputcsv($handle, ['', $type, '', '', '', '', '']);

                foreach ($coas as $coa) {
                    fputcsv($handle, [
                        $coa->coa_code,
                        $coa->name,
                        $coa->type,
                        $coa->opening_balance,
                        $coa->mutation_debit,
                        $coa->mutation_credit,
                        $coa->balance,
                    ]);
                }
            }

            $summary = $this->profitLossSummary();
            fputcsv($handle, ['RINGKASAN LABA RUGI', '', '', '', '', '', '']);
            fputcsv($handle, ['', 'TOTAL PENDAPATAN', 'RINGKASAN', $summary['opening']['revenue'], '', '', $summary['current']['revenue']]);
            fputcsv($handle, ['', 'TOTAL BEBAN', 'RINGKASAN', $summary['opening']['expense'], '', '', $summary['current']['expense']]);
            fputcsv($handle, ['', 'LABA/RUGI', 'RINGKASAN', $summary['opening']['profit'], '', '', $summary['current']['profit']]);
            fputcsv($handle, ['', 'TAKSIRAN PAJAK', 'RINGKASAN', $summary['opening']['estimated_tax'], '', '', $summary['current']['estimated_tax']]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function groupedCoas(bool $withSearch = true)
    {
        [$dateFrom, $dateTo] = $this->reportPeriod();
        $yearStart = Carbon::parse($dateTo)->startOfYear()->toDateString();

        // 1. Fetch opening balances (before date_from)
        $openingBalances = JournalEntry::query()
            ->select('coa_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
            ->whereHas('journal', function ($q) use ($dateFrom) {
                $q->whereDate('transaction_date', '<', $dateFrom)
                    ->where('status', 'APPROVED');

                if (!empty($this->filter_branch)) {
                    $q->where('branch_id', $this->filter_branch);
                }
            })
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        $profitLossOpeningBalances = JournalEntry::query()
            ->select('coa_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
            ->whereHas('journal', function ($q) use ($yearStart, $dateFrom) {
                $q->whereDate('transaction_date', '>=', $yearStart)
                    ->whereDate('transaction_date', '<', $dateFrom)
                    ->where('status', 'APPROVED')
                    ->where('reference_no', 'not like', 'CLS%');

                if (!empty($this->filter_branch)) {
                    $q->where('branch_id', $this->filter_branch);
                }
            })
            ->whereHas('coa', fn($q) => $q->whereIn('type', ['REVENUE', 'EXPENSE']))
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        $profitLossMutations = JournalEntry::query()
            ->select('coa_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
            ->whereHas('journal', function ($q) use ($dateFrom, $dateTo) {
                $q->whereDate('transaction_date', '>=', $dateFrom)
                    ->whereDate('transaction_date', '<=', $dateTo)
                    ->where('status', 'APPROVED')
                    ->where('reference_no', 'not like', 'CLS%');

                if (!empty($this->filter_branch)) {
                    $q->where('branch_id', $this->filter_branch);
                }
            })
            ->whereHas('coa', fn($q) => $q->whereIn('type', ['REVENUE', 'EXPENSE']))
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        // 2. Fetch mutations during period (date_from to date_to)
        $mutations = JournalEntry::query()
            ->select('coa_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
            ->whereHas('journal', function ($q) use ($dateFrom, $dateTo) {
                $q->whereDate('transaction_date', '>=', $dateFrom)
                    ->whereDate('transaction_date', '<=', $dateTo)
                    ->where('status', 'APPROVED');

                if (!empty($this->filter_branch)) {
                    $q->where('branch_id', $this->filter_branch);
                }
            })
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        // 3. Fetch all Leaf COAs and attach the values
        $search = trim($this->search);

        $coas = Coa::where('is_leaf', true)
            ->when($withSearch && $search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('coa_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->orderBy('coa_code')
            ->get()
            ->map(function ($coa) use ($openingBalances, $profitLossOpeningBalances, $mutations, $profitLossMutations, $dateFrom, $dateTo) {
                $open = in_array($coa->type, ['REVENUE', 'EXPENSE'], true)
                    ? $profitLossOpeningBalances->get($coa->id)
                    : $openingBalances->get($coa->id);
                $mut = in_array($coa->type, ['REVENUE', 'EXPENSE'], true)
                    ? $profitLossMutations->get($coa->id)
                    : $mutations->get($coa->id);

                $openDebit = $open->total_debit ?? 0;
                $openCredit = $open->total_credit ?? 0;

                if (in_array($coa->type, ['ASSET', 'EXPENSE'])) {
                    $coa->opening_balance = $openDebit - $openCredit;
                } else {
                    $coa->opening_balance = $openCredit - $openDebit;
                }

                $coa->mutation_debit = $mut->total_debit ?? 0;
                $coa->mutation_credit = $mut->total_credit ?? 0;

                if (in_array($coa->type, ['ASSET', 'EXPENSE'])) {
                    $coa->balance = $coa->opening_balance + $coa->mutation_debit - $coa->mutation_credit;
                } else {
                    $coa->balance = $coa->opening_balance + $coa->mutation_credit - $coa->mutation_debit;
                }

                if ($coa->coa_code === self::CURRENT_YEAR_PROFIT_COA) {
                    $opening = $this->currentYearProfitBalance(Carbon::parse($dateFrom)->subDay()->toDateString(), $dateTo);
                    $ending = $this->currentYearProfitBalance($dateTo, $dateTo);
                    $movement = round($ending - $opening, 2);

                    $coa->opening_balance = $opening;
                    $coa->mutation_debit = $movement < 0 ? abs($movement) : 0;
                    $coa->mutation_credit = $movement > 0 ? $movement : 0;
                    $coa->balance = $ending;
                }

                return $coa;
            })
            ->groupBy('type');

        return $coas;
    }

    private function profitLossSummary(): array
    {
        [$normalizedDateFrom, $normalizedDateTo] = $this->reportPeriod();
        $dateFrom = Carbon::parse($normalizedDateFrom);
        $dateTo = Carbon::parse($normalizedDateTo);
        $lastYearStart = $dateTo->copy()->subYear()->startOfYear();
        $lastYearEnd = $dateTo->copy()->subYear()->endOfYear();
        $groups = $this->groupedCoas(false);
        $opening = $this->profitLossTotals(
            (float) ($groups->get('REVENUE')?->sum('opening_balance') ?? 0),
            (float) ($groups->get('EXPENSE')?->sum('opening_balance') ?? 0),
            $dateFrom->copy()->subDay()->toDateString()
        );
        $current = $this->profitLossTotals(
            (float) ($groups->get('REVENUE')?->sum('balance') ?? 0),
            (float) ($groups->get('EXPENSE')?->sum('balance') ?? 0),
            $dateTo->toDateString()
        );
        $last = $this->profitLossTotalsBetween($lastYearStart->toDateString(), $lastYearEnd->toDateString());

        return [
            'opening' => $opening,
            'current' => $current,
            'last' => $last,
            'current_year' => $current['after_tax'],
            'last_year' => $last['profit'],
        ];
    }

    private function profitLossTotalsBetween(string $startDate, string $endDate): array
    {
        return $this->profitLossTotals(
            $this->sumByTypeBetween('REVENUE', $startDate, $endDate),
            $this->sumByTypeBetween('EXPENSE', $startDate, $endDate),
            $endDate
        );
    }

    private function currentYearProfitBalance(string $date, string $reportDate): float
    {
        $date = Carbon::parse($date);
        $yearStart = Carbon::parse($reportDate)->startOfYear();

        if ($date->lt($yearStart)) {
            return 0;
        }

        $profit = $this->profitLossTotalsBetween($yearStart->toDateString(), $date->toDateString())['after_tax'];
        $closed = $this->closedProfitBalance((int) $yearStart->year, $date->toDateString());

        return round($profit - $closed, 2);
    }

    private function closedProfitBalance(int $year, string $date): float
    {
        $entry = JournalEntry::query()
            ->select(DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
            ->whereHas('coa', fn ($query) => $query->where('coa_code', self::LAST_YEAR_PROFIT_COA))
            ->whereHas('journal', function ($query) use ($year, $date) {
                $query->where('status', 'APPROVED')
                    ->where('reference_no', 'like', "CLS{$year}%")
                    ->whereDate('transaction_date', '<=', $date);

                if (!empty($this->filter_branch)) {
                    $query->where('branch_id', $this->filter_branch);
                }
            })
            ->first();

        return round((float) ($entry->total_credit ?? 0) - (float) ($entry->total_debit ?? 0), 2);
    }

    private function profitLossTotals(float $revenue, float $expense, string $date): array
    {
        $profit = round($revenue - $expense, 2);
        $taxSetting = TaxSetting::effectiveFor($date);
        $taxRate = (float) ($taxSetting?->tax_rate ?? 0.5);
        $taxBase = $taxSetting?->calculation_base === 'TOTAL_REVENUE' ? $revenue : $profit;
        $estimatedTax = round(max(0, $taxBase) * ($taxRate / 100), 2);

        return [
            'revenue' => round($revenue, 2),
            'expense' => round($expense, 2),
            'profit' => $profit,
            'estimated_tax' => $estimatedTax,
            'after_tax' => round($profit - $estimatedTax, 2),
        ];
    }

    private function sumByTypeBetween(string $type, string $startDate, string $endDate): float
    {
        $entries = JournalEntry::query()
            ->with(['coa', 'journal'])
            ->whereHas('coa', function ($query) use ($type) {
                $query->where('type', $type)
                    ->where('is_leaf', true);
            })
            ->whereHas('journal', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'APPROVED')
                    ->whereDate('transaction_date', '>=', $startDate)
                    ->whereDate('transaction_date', '<=', $endDate)
                    ->where('reference_no', 'not like', 'CLS%');

                if (!empty($this->filter_branch)) {
                    $query->where('branch_id', $this->filter_branch);
                }
            })
            ->get();

        return (float) $entries->sum(function ($entry) use ($type) {
            return $type === 'EXPENSE'
                ? (float) $entry->debit - (float) $entry->credit
                : (float) $entry->credit - (float) $entry->debit;
        });
    }

    private function reportPeriod(): array
    {
        $dateFrom = Carbon::parse($this->date_from ?: now()->startOfMonth())->toDateString();
        $dateTo = Carbon::parse($this->date_to ?: now())->toDateString();

        if ($dateFrom > $dateTo) {
            return [$dateTo, $dateFrom];
        }

        return [$dateFrom, $dateTo];
    }
}
