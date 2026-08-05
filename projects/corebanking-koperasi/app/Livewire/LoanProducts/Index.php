<?php

namespace App\Livewire\LoanProducts;

use App\Models\LoanProduct;
use App\Models\Coa;
use App\Models\InsuranceProduct;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithLogout;
use App\Traits\ApprovesActions;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

class Index extends Component
{
    use WithPagination, WithLogout, ApprovesActions, LogsActivity;

    public $search = '';
    public $viewMode = 'list';

    // Form fields
    public $editingId = null;
    public $product_code, $name, $is_active = true, $is_diskonto = false;
    public $calculation_method = 'FLAT', $interest_rate_min = 0, $interest_rate_max = 0, $provision_rate = 0;
    public $admin_rate = 0, $penalty_rate = 0;
    public $tenor_min = 1, $tenor_max = 60, $tenor_type = 'MONTHS';

    // COA Mappings - Asset/Revenue/Liability Accounts
    public $principal_coa_id, $accrued_interest_coa_id, $accrued_interest_receivable_coa_id;
    public $interest_revenue_coa_id, $deferred_interest_coa_id, $provision_revenue_coa_id, $admin_fee_revenue_coa_id;
    public $insurance_revenue_coa_id, $flagging_revenue_coa_id, $penalty_revenue_coa_id;
    public $stamp_duty_payable_coa_id, $default_cash_coa_id, $default_bank_coa_id;
    public $ckpn_coa_id, $suspense_coa_id, $aba_transit_coa_id;

    protected $rules = [
        'product_code' => 'required|string|unique:loan_products,product_code',
        'name' => 'required|string|max:255',
        'is_diskonto' => 'boolean',
        'interest_rate_min' => 'required|numeric|min:0|max:100',
        'interest_rate_max' => 'required|numeric|min:0|gte:interest_rate_min|max:100',
        // Diskonto hanya boleh FLAT
        'calculation_method' => 'required|in:FLAT,EFFECTIVE,ANNUITY',
        'tenor_min' => 'required|integer|min:1',
        'tenor_max' => 'required|integer|min:1',
        'tenor_type' => 'required|in:MONTHS,DAYS',
        // Required Asset/Revenue COAs
        'principal_coa_id' => 'required|exists:coas,id',
        'interest_revenue_coa_id' => 'required|exists:coas,id',
        'deferred_interest_coa_id' => 'nullable|exists:coas,id',
        'default_cash_coa_id' => 'required|exists:coas,id',
        'default_bank_coa_id' => 'required|exists:coas,id',
        // Required settlement/transit COAs for production transactions and scheduler auto-debit
        'suspense_coa_id' => 'required|exists:coas,id',
        'aba_transit_coa_id' => 'required|exists:coas,id',
        // Optional but recommended COAs
        'accrued_interest_coa_id' => 'nullable|exists:coas,id',
        'accrued_interest_receivable_coa_id' => 'nullable|exists:coas,id',
        'provision_revenue_coa_id' => 'nullable|exists:coas,id',
        'admin_fee_revenue_coa_id' => 'nullable|exists:coas,id',
        'insurance_revenue_coa_id' => 'nullable|exists:coas,id',
        'flagging_revenue_coa_id' => 'nullable|exists:coas,id',
        'penalty_revenue_coa_id' => 'nullable|exists:coas,id',
        'stamp_duty_payable_coa_id' => 'nullable|exists:coas,id',
        'ckpn_coa_id' => 'nullable|exists:coas,id',
    ];

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Produk Pinjaman');
    }

    public function create()
    {
        $this->resetForm();
        $this->viewMode = 'create';
    }

    public function edit($id)
    {
        $product = LoanProduct::findOrFail($id);
        $this->editingId = $id;
        $this->fill($product->getAttributes());
        $this->viewMode = 'edit';
    }

    public function resetForm()
    {
        $this->reset([
            'editingId',
            'product_code',
            'name',
            'is_active',
            'is_diskonto',
            'calculation_method',
            'interest_rate_min',
            'interest_rate_max',
            'provision_rate',
            'admin_rate',
            'penalty_rate',
            'tenor_min',
            'tenor_max',
            'tenor_type',
            'principal_coa_id',
            'accrued_interest_coa_id',
            'accrued_interest_receivable_coa_id',
            'interest_revenue_coa_id',
            'deferred_interest_coa_id',
            'provision_revenue_coa_id',
            'admin_fee_revenue_coa_id',
            'insurance_revenue_coa_id',
            'flagging_revenue_coa_id',
            'penalty_revenue_coa_id',
            'stamp_duty_payable_coa_id',
            'default_cash_coa_id',
            'default_bank_coa_id',
            'ckpn_coa_id',
            'suspense_coa_id',
            'aba_transit_coa_id'
        ]);
        $this->is_active = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editingId) {
            $rules['product_code'] = 'required|string|unique:loan_products,product_code,' . $this->editingId;
        }

        $this->validate($rules);

        $data = collect($this->all())
            ->only((new LoanProduct)->getFillable())
            ->all();
        $data['is_active']   = (bool)$this->is_active;
        $data['is_diskonto'] = (bool)$this->is_diskonto;
        $data['deferred_interest_coa_id'] = $this->deferred_interest_coa_id ?: null;

        // Diskonto hanya boleh FLAT — paksa jika ada
        if ($data['is_diskonto']) {
            $data['calculation_method'] = 'FLAT';
            if (blank($this->deferred_interest_coa_id)) {
                $this->addError('deferred_interest_coa_id', 'COA bunga diterima dimuka wajib diisi untuk produk diskonto.');
                return;
            }
        }

        if ((float) $this->provision_rate > 0 && blank($this->provision_revenue_coa_id)) {
            $this->addError('provision_revenue_coa_id', 'COA pendapatan provisi wajib diisi jika tarif provisi lebih dari 0.');
            return;
        }

        if ((float) $this->admin_rate > 0 && blank($this->admin_fee_revenue_coa_id)) {
            $this->addError('admin_fee_revenue_coa_id', 'COA pendapatan administrasi wajib diisi jika tarif admin lebih dari 0.');
            return;
        }

        if ((float) $this->penalty_rate > 0 && blank($this->penalty_revenue_coa_id)) {
            $this->addError('penalty_revenue_coa_id', 'COA pendapatan denda wajib diisi jika tarif denda lebih dari 0.');
            return;
        }

        // Clean nominal fields
        foreach (['admin_rate', 'provision_rate', 'penalty_rate'] as $field) {
            if (isset($data[$field])) $data[$field] = (float)$data[$field];
        }
        $data['default_bank_coa_id'] = $this->default_bank_coa_id;

        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $action = $this->editingId ? 'UPDATE' : 'CREATE';
        $res = $this->interceptAction('loan_products', $action, $data, $this->editingId, $this->editingId ? LoanProduct::find($this->editingId)->toArray() : null);

        if ($res === 'PENDING') {
            session()->flash('success', 'Perubahan produk telah dikirim ke antrean persetujuan.');
            $this->logActivity($action . '_REQUEST', "Mengajukan pembuatan/perubahan produk kredit: " . $this->name, null, $data);
        } else {
            session()->flash('success', 'Produk berhasil disimpan.');
            $this->logActivity($action, "Berhasil menyimpan produk kredit: " . $this->name, null, $data);
        }

        $this->viewMode = 'list';
    }

    public function render()
    {
        $query = LoanProduct::query();
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('product_code', 'like', '%' . $this->search . '%');
        }

        return view('livewire.loan-products.index', [
            'products' => $query->latest()->paginate(10),
            'assetCoas' => Coa::leaf()->where('type', 'ASSET')->active()->orderBy('coa_code')->get(),
            'revenueCoas' => Coa::leaf()->where('type', 'REVENUE')->active()->orderBy('coa_code')->get(),
            'liabilityCoas' => Coa::leaf()->where('type', 'LIABILITY')->active()->orderBy('coa_code')->get(),
            'expenseCoas' => Coa::leaf()->where('type', 'EXPENSE')->active()->orderBy('coa_code')->get(),
            'insuranceProducts' => InsuranceProduct::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
