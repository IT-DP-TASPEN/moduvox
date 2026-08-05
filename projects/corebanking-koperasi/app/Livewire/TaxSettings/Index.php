<?php

namespace App\Livewire\TaxSettings;

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\TaxSetting;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithLogout, LogsActivity;

    public ?int $editingId = null;
    public string $name = 'PPH BADAN FINAL';
    public string $tax_rate = '0.50';
    public string $calculation_base = 'PROFIT_BEFORE_TAX';
    public ?int $expense_coa_id = null;
    public ?int $payable_coa_id = null;
    public ?string $effective_from = '2025-01-01';
    public ?string $effective_to = '2025-12-31';
    public bool $is_active = true;

    public string $preview_start = '2025-01-01';
    public string $preview_end = '2025-12-31';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'calculation_base' => 'required|in:TOTAL_REVENUE,PROFIT_BEFORE_TAX',
            'expense_coa_id' => 'required|exists:coas,id',
            'payable_coa_id' => 'required|exists:coas,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'boolean',
        ];
    }

    public function mount(): void
    {
        $setting = TaxSetting::with(['expenseCoa', 'payableCoa'])->where('is_active', true)->latest('effective_from')->first()
            ?? TaxSetting::with(['expenseCoa', 'payableCoa'])->latest()->first();

        if ($setting) {
            $this->fillFromSetting($setting);
        } else {
            $this->expense_coa_id = Coa::where('coa_code', '523100')->value('id');
            $this->payable_coa_id = Coa::where('coa_code', '219060')->value('id');
        }

        $this->logActivity('NAVIGATE', 'Pengaturan Pajak');
    }

    public function loadSetting(int $settingId): void
    {
        $this->fillFromSetting(TaxSetting::findOrFail($settingId));
    }

    private function fillFromSetting(TaxSetting $setting): void
    {
        $this->editingId = $setting->id;
        $this->name = $setting->name;
        $this->tax_rate = number_format((float) $setting->tax_rate, 2, '.', '');
        $this->calculation_base = $setting->calculation_base;
        $this->expense_coa_id = $setting->expense_coa_id;
        $this->payable_coa_id = $setting->payable_coa_id;
        $this->effective_from = $setting->effective_from?->format('Y-m-d');
        $this->effective_to = $setting->effective_to?->format('Y-m-d');
        $this->is_active = (bool) $setting->is_active;
    }

    public function createNew(): void
    {
        $this->editingId = null;
        $this->name = 'PPH BADAN FINAL';
        $this->tax_rate = '0.50';
        $this->calculation_base = 'PROFIT_BEFORE_TAX';
        $this->expense_coa_id = Coa::where('coa_code', '523100')->value('id');
        $this->payable_coa_id = Coa::where('coa_code', '219060')->value('id');
        $this->effective_from = now()->startOfYear()->toDateString();
        $this->effective_to = now()->endOfYear()->toDateString();
        $this->is_active = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $this->validateNoOverlap($data);

        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $setting = TaxSetting::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->editingId = $setting->id;
        session()->flash('success', 'PENGATURAN PAJAK BERHASIL DISIMPAN.');
        $this->logActivity('UPDATE', 'Menyimpan pengaturan pajak: ' . $this->name, $setting, $data);
    }

    private function validateNoOverlap(array $data): void
    {
        if (! $data['is_active']) {
            return;
        }

        $from = $data['effective_from'];
        $to = $data['effective_to'] ?? null;

        $overlap = TaxSetting::query()
            ->where('is_active', true)
            ->when($this->editingId, fn($query) => $query->whereKeyNot($this->editingId))
            ->whereDate('effective_from', '<=', $to ?: '9999-12-31')
            ->where(function ($query) use ($from) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $from);
            })
            ->exists();

        if ($overlap) {
            $this->addError('effective_from', 'Periode pajak aktif tidak boleh overlap dengan periode aktif lain.');
            throw \Illuminate\Validation\ValidationException::withMessages([
                'effective_from' => 'Periode pajak aktif tidak boleh overlap dengan periode aktif lain.',
            ]);
        }
    }

    public function getPreviewProperty(): array
    {
        $totalRevenue = $this->sumByType('REVENUE');
        $totalExpenseBeforeTax = $this->sumByType('EXPENSE', ['523']);
        $profitBeforeTax = $totalRevenue - $totalExpenseBeforeTax;
        $baseAmount = $this->calculation_base === 'PROFIT_BEFORE_TAX'
            ? $profitBeforeTax
            : $totalRevenue;
        $taxAmount = round(max(0, $baseAmount) * ((float) $this->tax_rate / 100), 0);

        return [
            'total_revenue' => $totalRevenue,
            'expense_before_tax' => $totalExpenseBeforeTax,
            'profit_before_tax' => $profitBeforeTax,
            'base_amount' => $baseAmount,
            'tax_amount' => $taxAmount,
            'net_profit' => $profitBeforeTax - $taxAmount,
        ];
    }

    private function sumByType(string $type, array $excludePrefixes = []): float
    {
        $entries = JournalEntry::query()
            ->with(['coa', 'journal'])
            ->whereHas('journal', function ($q) {
                $q->where('status', 'APPROVED')
                    ->when($this->preview_start, fn($q2) => $q2->whereDate('transaction_date', '>=', $this->preview_start))
                    ->when($this->preview_end, fn($q2) => $q2->whereDate('transaction_date', '<=', $this->preview_end));
            })
            ->whereHas('coa', function ($q) use ($type, $excludePrefixes) {
                $q->where('type', $type)->where('is_leaf', true);
                foreach ($excludePrefixes as $prefix) {
                    $q->where('coa_code', 'not like', $prefix . '%');
                }
            })
            ->get();

        return (float) $entries->sum(function ($entry) use ($type) {
            if ($type === 'ASSET' || $type === 'EXPENSE') {
                return (float) $entry->debit - (float) $entry->credit;
            }

            return (float) $entry->credit - (float) $entry->debit;
        });
    }

    public function render()
    {
        return view('livewire.tax-settings.index', [
            'settings' => TaxSetting::with(['expenseCoa', 'payableCoa'])->latest('effective_from')->get(),
            'expenseCoas' => Coa::leaf()->active()->where('type', 'EXPENSE')->orderBy('coa_code')->get(),
            'liabilityCoas' => Coa::leaf()->active()->where('type', 'LIABILITY')->orderBy('coa_code')->get(),
        ])->layout('layouts.app');
    }
}
