<?php

namespace App\Livewire\DepositProducts;

use App\Models\DepositProduct;
use App\Models\Coa;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithLogout;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithLogout, ApprovesActions, LogsActivity;

    public $user, $role;
    public $search = '';
    public $viewMode = 'list'; // list, create, edit

    // Form fields
    public $editingId = null;
    public $product_code, $name, $is_active = true;
    public $min_term = 1, $max_term, $term_unit = 'MONTH';
    public $min_amount = 0, $max_amount;
    public $min_interest_rate = 0, $max_interest_rate = 0;
    public $interest_period = 'MONTHLY', $interest_calculation_type = 'MONTHLY';
    public $tax_rate = 0;

    // COA Mappings
    public $liability_coa_id, $interest_expense_coa_id, $accrued_interest_payable_coa_id;
    public $tax_liability_coa_id, $admin_fee_revenue_coa_id, $kas_coa_id, $default_cash_coa_id, $default_bank_coa_id;

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Produk Simpanan Berjangka');
    }

    protected function rules()
    {
        return [
            'product_code' => 'required|string|unique:deposit_products,product_code,' . $this->editingId,
            'name' => 'required|string|max:255',
            'min_term' => 'required|integer|min:1',
            'max_term' => 'nullable|integer|gte:min_term',
            'term_unit' => 'required|in:MONTH,MONTHS,DAY,DAYS',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gte:min_amount',
            'min_interest_rate' => 'required|numeric|min:0|max:100',
            'max_interest_rate' => 'required|numeric|gte:min_interest_rate|max:100',
            'interest_period' => 'required|string',
            'interest_calculation_type' => 'required|in:MONTHLY,DAILY',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'liability_coa_id' => 'required|exists:coas,id',
            'interest_expense_coa_id' => 'required|exists:coas,id',
            'kas_coa_id' => 'required|exists:coas,id',
            'default_bank_coa_id' => 'required|exists:coas,id',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->viewMode = 'create';
    }

    public function edit($id)
    {
        $product = DepositProduct::findOrFail($id);
        $this->editingId = $id;
        $this->fill($product->toArray());
        $this->term_unit = $this->normalizeTermUnit($this->term_unit);
        $this->interest_calculation_type = $this->normalizeCalculationType($this->interest_calculation_type);
        $this->min_amount = (float) $this->min_amount;
        $this->max_amount = $this->max_amount === null ? null : (float) $this->max_amount;
        $this->kas_coa_id ??= $this->default_cash_coa_id;
        $this->viewMode = 'edit';
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'editingId',
            'product_code',
            'name',
            'is_active',
            'min_term',
            'max_term',
            'term_unit',
            'min_amount',
            'max_amount',
            'min_interest_rate',
            'max_interest_rate',
            'interest_period',
            'interest_calculation_type',
            'tax_rate',
            'liability_coa_id',
            'interest_expense_coa_id',
            'accrued_interest_payable_coa_id',
                'tax_liability_coa_id',
                'admin_fee_revenue_coa_id',
                'kas_coa_id',
                'default_cash_coa_id',
                'default_bank_coa_id'
        ]);
        $this->is_active = true;
        $this->term_unit = 'MONTH';
        $this->interest_calculation_type = 'MONTHLY';
        $this->interest_period = 'MONTHLY';
        $this->tax_rate = 0;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'product_code' => $this->product_code,
            'name' => $this->name,
            'is_active' => (bool)$this->is_active,
            'min_term' => (int)$this->min_term,
            'max_term' => $this->max_term ? (int)$this->max_term : null,
            'term_unit' => $this->normalizeTermUnit($this->term_unit),
            'min_amount' => (float)$this->min_amount,
            'max_amount' => $this->max_amount ? (float)$this->max_amount : null,
            'min_interest_rate' => (float)$this->min_interest_rate,
            'max_interest_rate' => (float)$this->max_interest_rate,
            'interest_period' => $this->interest_period,
            'interest_calculation_type' => $this->normalizeCalculationType($this->interest_calculation_type),
            'tax_rate' => (float)$this->tax_rate,
            'liability_coa_id' => $this->liability_coa_id,
            'interest_expense_coa_id' => $this->interest_expense_coa_id,
            'accrued_interest_payable_coa_id' => $this->accrued_interest_payable_coa_id,
            'tax_liability_coa_id' => $this->tax_liability_coa_id,
            'admin_fee_revenue_coa_id' => $this->admin_fee_revenue_coa_id,
            'kas_coa_id' => $this->kas_coa_id,
            'default_cash_coa_id' => $this->kas_coa_id,
            'default_bank_coa_id' => $this->default_bank_coa_id,
            'updated_by' => Auth::id(),
        ];

        if (!$this->editingId) {
            $data['created_by'] = Auth::id();
        }

        $action = $this->editingId ? 'UPDATE' : 'CREATE';
        $res = $this->interceptAction('deposit_products', $action, $data, $this->editingId, $this->editingId ? DepositProduct::find($this->editingId)->toArray() : null);

        if ($res === 'PENDING') {
            session()->flash('success', 'Usulan produk simpanan berjangka telah dikirim untuk persetujuan.');
        } else {
            session()->flash('success', 'Produk simpanan berjangka berhasil disimpan.');
        }

        $this->viewMode = 'list';
        $this->resetForm();
    }

    private function normalizeTermUnit(?string $unit): string
    {
        return match (strtoupper((string) $unit)) {
            'DAY', 'DAYS' => 'DAY',
            default => 'MONTH',
        };
    }

    private function normalizeCalculationType(?string $type): string
    {
        return strtoupper((string) $type) === 'DAILY' ? 'DAILY' : 'MONTHLY';
    }

    public function render()
    {
        $query = DepositProduct::query();
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('product_code', 'like', '%' . $this->search . '%');
        }

        return view('livewire.deposit-products.index', [
            'products' => $query->latest()->paginate(10),
            'assetCoas' => Coa::where('is_leaf', true)->where('type', 'ASSET')->where('is_active', true)->orderBy('coa_code')->get(),
            'liabilityCoas' => Coa::where('is_leaf', true)->where('type', 'LIABILITY')->where('is_active', true)->orderBy('coa_code')->get(),
            'revenueCoas' => Coa::where('is_leaf', true)->where('type', 'REVENUE')->where('is_active', true)->orderBy('coa_code')->get(),
            'expenseCoas' => Coa::where('is_leaf', true)->where('type', 'EXPENSE')->where('is_active', true)->orderBy('coa_code')->get(),
        ])->layout('layouts.app');
    }
}
