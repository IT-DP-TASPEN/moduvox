<?php

namespace App\Livewire\SavingProducts;

use App\Models\SavingProduct;
use App\Models\Coa;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithLogout;
use App\Traits\ApprovesActions;
use Illuminate\Support\Facades\Auth;

use App\Traits\LogsActivity;

class Index extends Component
{
    use WithPagination, WithLogout, ApprovesActions, LogsActivity;

    public $user, $role;
    public $search = '';
    public $viewMode = 'list';

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Produk Simpanan');
    }
    
    // Form fields
    public $editingId = null;
    public $product_code, $name, $is_active = true;
    public $interest_calculation_type = 'DAILY', $interest_rate = 0, $interest_payment_period = 'MONTHLY';
    public $min_initial_deposit = 0, $min_balance = 0, $max_balance = null, $has_overdraft = false;
    public $has_admin_fee = false, $admin_fee = 0;
    public $has_closing_fee = false, $closed_fee = 0;
    public $min_balance_penalty = 0, $min_balance_penalty_period = null;
    public $has_automatic_dormant = false, $no_transaction_monthly_terms = 6, $no_transaction_penalty = 0;
    public $dormant_penalty_grace_period = null, $dormant_penalty_amount = 0;
    public $has_tax_on_interest = true, $tax_rate = 20;
    
    // COA Mappings
    public $liability_coa_id, $interest_expense_coa_id, $admin_fee_revenue_coa_id;
    public $tax_liability_coa_id, $accrued_interest_payable_coa_id, $default_cash_coa_id;
    public $default_bank_coa_id, $aba_transit_coa_id;
    
    public $fee_name, $fee_amount = 0, $fee_type;

    protected $rules = [
        'product_code' => 'required|string|unique:saving_products,product_code',
        'name' => 'required|string|max:255',
        'interest_rate' => 'required|numeric|min:0|max:100',
        'interest_payment_period' => 'required|in:MONTHLY,QUARTERLY,ANNUALLY',
        'has_tax_on_interest' => 'required|boolean',
        'tax_rate' => 'required|numeric|min:0|max:100',
        'min_initial_deposit' => 'nullable|numeric|min:0',
        'min_balance' => 'nullable|numeric|min:0',
        'max_balance' => 'nullable|numeric|min:0',
        'has_overdraft' => 'required|boolean',
        'admin_fee' => 'nullable|numeric|min:0',
        'closed_fee' => 'nullable|numeric|min:0',
        'min_balance_penalty' => 'nullable|numeric|min:0',
        'min_balance_penalty_period' => 'nullable|integer|min:0',
        'dormant_penalty_amount' => 'nullable|numeric|min:0',
        'no_transaction_penalty' => 'nullable|numeric|min:0',
        'fee_amount' => 'nullable|numeric|min:0',
        'liability_coa_id' => 'required|exists:coas,id',
        'interest_expense_coa_id' => 'required|exists:coas,id',
        'admin_fee_revenue_coa_id' => 'nullable|exists:coas,id',
        'default_cash_coa_id' => 'required|exists:coas,id',
        'default_bank_coa_id' => 'nullable|exists:coas,id',
        'aba_transit_coa_id' => 'nullable|exists:coas,id',
    ];

    public function create()
    {
        $this->resetForm();
        $this->viewMode = 'create';
    }

    public function edit($id)
    {
        $product = SavingProduct::findOrFail($id);
        $this->editingId = $id;
        $this->fill($product->toArray());
        $this->viewMode = 'edit';
    }

    public function resetForm()
    {
        $this->reset([
            'editingId', 'product_code', 'name', 'is_active', 'interest_calculation_type', 
            'interest_rate', 'interest_payment_period', 'min_initial_deposit', 'min_balance', 
            'max_balance', 'has_overdraft', 'has_admin_fee', 'admin_fee', 'has_closing_fee', 
            'closed_fee', 'min_balance_penalty', 'min_balance_penalty_period', 'has_automatic_dormant', 
            'no_transaction_monthly_terms', 'no_transaction_penalty', 'dormant_penalty_grace_period', 
            'dormant_penalty_amount', 'has_tax_on_interest', 'tax_rate', 'liability_coa_id', 
            'interest_expense_coa_id', 'admin_fee_revenue_coa_id', 'tax_liability_coa_id', 
            'accrued_interest_payable_coa_id', 'default_cash_coa_id', 'default_bank_coa_id', 'aba_transit_coa_id', 'fee_name', 'fee_amount', 'fee_type'
        ]);
        $this->is_active = true;
        $this->interest_rate = 0;
        $this->tax_rate = 20;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editingId) {
            $rules['product_code'] = 'required|string|unique:saving_products,product_code,' . $this->editingId;
        }
        
        $this->validate($rules);

        $data = collect($this->all())
            ->only((new SavingProduct)->getFillable())
            ->all();
        $data['is_active'] = (bool)$this->is_active;
        $data['has_overdraft'] = (bool)$this->has_overdraft;
        $data['has_admin_fee'] = (bool)$this->has_admin_fee;
        $data['has_closing_fee'] = (bool)$this->has_closing_fee;
        $data['has_automatic_dormant'] = (bool)$this->has_automatic_dormant;
        $data['has_tax_on_interest'] = (bool)$this->has_tax_on_interest;
        
        // Clean nominal fields from formatting issues
        $nominalFields = [
            'min_initial_deposit', 'min_balance', 'max_balance', 'admin_fee', 
            'closed_fee', 'min_balance_penalty', 'no_transaction_penalty', 
            'dormant_penalty_amount', 'fee_amount'
        ];
        foreach ($nominalFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = (int)$data[$field];
            }
        }

        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        
        // Intercept for Approval
        $action = $this->editingId ? 'UPDATE' : 'CREATE';
        $res = $this->interceptAction('saving_products', $action, $data, $this->editingId, $this->editingId ? SavingProduct::find($this->editingId)->toArray() : null);

        if ($res === 'PENDING') {
            session()->flash('success', 'Perubahan produk telah dikirim ke antrean persetujuan.');
            $this->logActivity($action . '_REQUEST', "Mengajukan " . ($this->editingId ? 'perubahan' : 'pembuatan') . " produk simpanan: " . $this->name, null, $data);
        } else {
            session()->flash('success', 'Produk berhasil disimpan.');
            $this->logActivity($action, "Berhasil menyimpan produk simpanan: " . $this->name, null, $data);
        }

        $this->viewMode = 'list';
    }

    public function render()
    {
        $query = SavingProduct::query();
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('product_code', 'like', '%' . $this->search . '%');
        }

        return view('livewire.saving-products.index', [
            'products' => $query->latest()->paginate(10),
            // Pass pre-filtered COAs for the Accounting Rules section
            'assetCoas' => Coa::where('is_leaf', true)->where('type', 'ASSET')->where('is_active', true)->orderBy('coa_code')->get(),
            'liabilityEquityCoas' => Coa::where('is_leaf', true)->whereIn('type', ['LIABILITY', 'EQUITY'])->where('is_active', true)->orderBy('coa_code')->get(),
            'liabilityCoas' => Coa::where('is_leaf', true)->where('type', 'LIABILITY')->where('is_active', true)->orderBy('coa_code')->get(),
            'revenueCoas' => Coa::where('is_leaf', true)->where('type', 'REVENUE')->where('is_active', true)->orderBy('coa_code')->get(),
            'expenseCoas' => Coa::where('is_leaf', true)->where('type', 'EXPENSE')->where('is_active', true)->orderBy('coa_code')->get(),
        ])->layout('layouts.app');
    }
}
