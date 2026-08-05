<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use App\Models\Cif;
use App\Models\LoanProduct;
use App\Models\Branch;
use App\Models\MarketingMaster;
use App\Models\SavingAccount;
use App\Services\LoanOperationService;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Origination extends Component
{
    use ApprovesActions, LogsActivity;

    // ===== SECTION 1: Data Nasabah =====
    public $cif_id;
    public $saving_account_id;
    public $searchCif = '';
    public $selectedCif = null;
    public $availableSavingAccounts = [];

    // ===== SECTION 2: Produk & Parameter Kredit =====
    public $loan_product_id;
    public $is_using_insurance = false;
    public $insurance_product_id;
    public $insurance_rate = 0;
    public $branch_id;
    public $marketing_id;
    public $principal_amount;
    public $tenor = 12;
    public $tenor_type = 'MONTHS';
    public $interest_rate;
    public $calculation_method = 'FLAT';
    public $disbursement_date;

    // ===== SECTION 3: Data Pemohon =====
    public $reason;
    public $applicant_purpose = 'MODAL_USAHA';    // Tujuan Penggunaan
    public $applicant_occupation;                  // Pekerjaan
    public $applicant_company_name;                // Nama Perusahaan / Usaha
    public $applicant_company_address;             // Alamat Perusahaan
    public $applicant_monthly_income;              // Penghasilan Per Bulan
    public $applicant_monthly_expense;             // Pengeluaran Per Bulan
    public $applicant_other_income;                // Penghasilan Lain-lain

    // ===== SECTION 4: Agunan / Jaminan =====
    public $collateral_type = 'KL1';              // Kolektibilitas
    public $collateral_description;               // Jenis/Keterangan Agunan
    public $collateral_certificate_no;            // No. Sertifikat / BPKB
    public $collateral_value;                     // Nilai Taksasi
    public $collateral_address;                   // Alamat / Lokasi Agunan

    // ===== SECTION 5: Data Penjamin =====
    public $guarantor_name;
    public $guarantor_nik;
    public $guarantor_phone;
    public $guarantor_address;
    public $guarantor_relation;

    // ===== SECTION 6: Biaya-Biaya =====
    public $provision_rate = 0;
    public $admin_rate = 0;
    public $provision_fee = 0;
    public $admin_fee = 0;
    public $insurance_fee = 0;
    public $flagging_fee = 0;
    public $stamp_duty_fee = 0;
    public $prepaid_count = 0;
    public $blocked_count = 0;
    public $prepaid_amount = 0;
    public $blocked_amount = 0;
    public $analyst_note;
    public $analyst_recommendation;

    // ===== DISKONTO =====
    public $is_diskonto_product  = false;  // Di-set dari produk yang dipilih
    public $diskonto_upfront_amount = 0;   // Bunga di muka = bunga_bulanan × tenor

    public function mount()
    {
        $this->disbursement_date = now()->format('Y-m-d');
        $this->branch_id = Auth::user()->branch_id ?? null;
        $this->logActivity('NAVIGATE', 'Pendaftaran Pinjaman');
    }

    public function updatedSearchCif()
    {
        $this->selectedCif = null;
        $this->cif_id = null;
        $this->availableSavingAccounts = [];
        $this->saving_account_id = null;
    }

    public function selectCif($id)
    {
        $this->selectedCif = Cif::find($id);
        $this->cif_id = $id;
        $this->searchCif = '';
        $this->cifResults = [];

        $this->availableSavingAccounts = SavingAccount::where('cif_id', $this->cif_id)
            ->where('status', 'ACTIVE')
            ->get();

        $this->saving_account_id = null;

        // Auto-fill occupation from CIF data
        if ($this->selectedCif->occupation) {
            $this->applicant_occupation = $this->selectedCif->occupation;
        }

        $this->lookupInsuranceRate();
        $this->calculateFees();
    }

    public function updatedLoanProductId()
    {
        $product = LoanProduct::find($this->loan_product_id);
        if ($product) {
            $this->interest_rate = $product->interest_rate_min;
            $this->provision_rate = $product->provision_rate;
            $this->admin_rate = $product->admin_rate;

            // ── Diskonto: wajib FLAT, tidak bisa diubah ──────────────────
            $this->is_diskonto_product = (bool)($product->is_diskonto ?? false);
            if ($this->is_diskonto_product) {
                $this->calculation_method = 'FLAT';
                // Reset prepaid dan blocked untuk diskonto (tidak relevan)
                $this->prepaid_count = 0;
                $this->blocked_count = 0;
                $this->prepaid_amount = 0;
                $this->blocked_amount = 0;
            } else {
                $this->calculation_method = $product->calculation_method;
            }

            $this->calculateFees();
        }
    }

    public function updatedProvisionRate()
    {
        $this->calculateFees();
    }
    public function updatedAdminRate()
    {
        $this->calculateFees();
    }
    public function updatedInsuranceRate()
    {
        $this->calculateFees();
    }

    public function updatedPrincipalAmount()
    {
        $this->calculateFees();
    }

    public function updatedTenor()
    {
        $this->lookupInsuranceRate();
        $this->calculateFees();
    }

    public function updatedIsUsingInsurance()
    {
        if (!$this->is_using_insurance) {
            $this->insurance_product_id = null;
            $this->insurance_rate = 0;
            $this->insurance_fee = 0;
        } else {
            $this->lookupInsuranceRate();
        }
        $this->calculateFees();
    }

    public function updatedInsuranceProductId()
    {
        $this->lookupInsuranceRate();
        $this->calculateFees();
    }

    public function lookupInsuranceRate()
    {
        if (!$this->is_using_insurance || !$this->insurance_product_id || !$this->selectedCif || !$this->tenor) {
            return;
        }

        $birthDate = $this->selectedCif->birth_date;
        if (!$birthDate) return;

        $age = \Carbon\Carbon::parse($birthDate)->age;
        $tenorMonths = (int)$this->tenor;

        $rateRecord = \App\Models\InsuranceRate::where('insurance_product_id', $this->insurance_product_id)
            ->where('age', $age)
            ->where('tenor_months', '>=', $tenorMonths)
            ->orderBy('tenor_months', 'asc')
            ->first();

        if ($rateRecord) {
            $this->insurance_rate = round($rateRecord->rate, 2);
        } else {
            $this->insurance_rate = 0;
        }
    }


    public function calculateFees()
    {
        $product = LoanProduct::find($this->loan_product_id);
        if (!$product) return;

        $principal = $this->parseAmount($this->principal_amount);

        // Provisi based on rate
        if ($this->provision_rate > 0) {
            $this->provision_fee = round(($principal * $this->provision_rate) / 100);
        } else {
            $this->provision_fee = 0;
        }

        // Admin fee based on rate
        if ($this->admin_rate > 0) {
            $this->admin_fee = round(($principal * $this->admin_rate) / 100);
        } else {
            $this->admin_fee = 0;
        }

        // Insurance based on rate
        if ($this->insurance_rate > 0) {
            $this->insurance_fee = round(($principal * $this->insurance_rate) / 100);
        } else {
            $this->insurance_fee = 0;
        }

        // ── Diskonto: hitung bunga di muka ───────────────────────────────
        if ($this->is_diskonto_product && $principal > 0 && $this->interest_rate > 0 && $this->tenor > 0) {
            $monthlyInterest = $principal * ((float)$this->interest_rate / 100 / 12);
            $this->diskonto_upfront_amount = round($monthlyInterest * (int)$this->tenor, 2);
            // Diskonto tidak pakai prepaid/blocked
            $this->prepaid_amount = 0;
            $this->blocked_amount = 0;
        } else {
            $this->diskonto_upfront_amount = 0;

            // Calculate Prepaid Installment and Blocked Savings if simulasi is available
            $simulasi = $this->getSimulasiProperty(app(LoanOperationService::class));
            if (!empty($simulasi)) {
                $oneInstallment = $simulasi[0]['total_amount'] ?? 0;
                $this->prepaid_amount = round($oneInstallment * (int)$this->prepaid_count);
                $this->blocked_amount = round($oneInstallment * (int)$this->blocked_count);
            } else {
                $this->prepaid_amount = 0;
                $this->blocked_amount = 0;
            }
        }
    }

    protected function rules()
    {
        $product = LoanProduct::find($this->loan_product_id);
        $minRate = $product ? $product->interest_rate_min : 0;
        $maxRate = $product ? $product->interest_rate_max : 100;
        $minTenor = $product ? $product->tenor_min : 1;
        $maxTenor = $product ? $product->tenor_max : 120;

        return [
            // Section 1: Nasabah
            'cif_id'              => 'required|exists:cifs,id',
            'saving_account_id'   => 'required|exists:saving_accounts,id',

            // Section 2: Produk & Parameter
            'loan_product_id'     => 'required|exists:loan_products,id',
            'is_using_insurance'  => 'boolean',
            'insurance_product_id' => 'required_if:is_using_insurance,true|nullable|exists:insurance_products,id',
            'insurance_rate'      => 'required_if:is_using_insurance,true|nullable|numeric|min:0|max:100',
            'branch_id'           => 'required|exists:branches,id',
            'principal_amount'    => 'required|numeric|min:100000',
            'tenor'               => "required|integer|min:{$minTenor}|max:{$maxTenor}",
            'calculation_method'  => 'required|in:FLAT,EFFECTIVE,ANNUITY',
            'interest_rate'       => "required|numeric|min:{$minRate}|max:{$maxRate}",
            'disbursement_date'   => 'required|date',

            // Section 3: Data Pemohon
            'applicant_purpose'           => 'required|string',
            'applicant_occupation'        => 'nullable|string|max:100',
            'applicant_company_name'      => 'nullable|string|max:200',
            'applicant_company_address'   => 'nullable|string|max:500',
            'applicant_monthly_income'    => 'nullable|numeric|min:0',
            'applicant_monthly_expense'   => 'nullable|numeric|min:0',
            'applicant_other_income'      => 'nullable|numeric|min:0',
            'reason'                      => 'nullable|string|max:1000',

            // Section 4: Agunan
            'collateral_type'             => 'required|string',
            'collateral_description'      => 'nullable|string|max:500',
            'collateral_certificate_no'   => 'nullable|string|max:100',
            'collateral_value'            => 'nullable|numeric|min:0',
            'collateral_address'          => 'nullable|string|max:500',

            // Section 5: Penjamin
            'guarantor_name'              => 'nullable|string|max:200',
            'guarantor_nik'               => 'nullable|string|max:20',
            'guarantor_phone'             => 'nullable|string|max:20',
            'guarantor_address'           => 'nullable|string|max:500',
            'guarantor_relation'          => 'nullable|string|max:50',

            // Section 6: Biaya
            'provision_rate' => 'nullable|numeric|min:0|max:100',
            'admin_rate'     => 'nullable|numeric|min:0|max:100',
            'provision_fee'  => 'nullable|numeric|min:0',
            'admin_fee'      => 'nullable|numeric|min:0',
            'insurance_fee'  => 'nullable|numeric|min:0',
            'flagging_fee'   => 'nullable|numeric|min:0',
            'stamp_duty_fee' => 'nullable|numeric|min:0',
            'prepaid_count'  => 'nullable|integer|min:0',
            'blocked_count'  => 'nullable|integer|min:0',
        ];
    }

    protected function parseAmount($value): float
    {
        return (float) str_replace(['.', ','], ['', '.'], $value ?? 0);
    }

    protected function loanPayload(): array
    {
        return [
            'cif_id'              => $this->cif_id,
            'saving_account_id'   => $this->saving_account_id,
            'loan_product_id'     => $this->loan_product_id,
            'branch_id'           => $this->branch_id ?? Cif::find($this->cif_id)->branch_id ?? 1,
            'marketing_id'        => $this->marketing_id ?: null,

            // Financial
            'principal_amount'    => $this->parseAmount($this->principal_amount),
            'tenor'               => $this->tenor,
            'tenor_type'          => $this->tenor_type,
            'interest_rate'       => $this->interest_rate,
            'calculation_method'  => $this->calculation_method,
            'provision_fee'       => $this->provision_fee,
            'admin_fee'           => $this->admin_fee,
            'insurance_product_id' => $this->insurance_product_id,
            'insurance_rate'      => $this->insurance_rate,
            'insurance_fee'       => $this->insurance_fee,
            'flagging_fee'        => $this->parseAmount($this->flagging_fee),
            'stamp_duty_fee'      => $this->parseAmount($this->stamp_duty_fee),
            'prepaid_installment_count' => $this->prepaid_count,
            'prepaid_installment_amount' => $this->prepaid_amount,
            'blocked_savings_count'      => $this->blocked_count,
            'blocked_savings_amount'     => $this->blocked_amount,
            'is_diskonto'                => $this->is_diskonto_product,
            'diskonto_upfront_amount'    => $this->diskonto_upfront_amount,
            'analyst_notes'       => $this->analyst_note,
            'analyst_recommendation' => $this->analyst_recommendation,

            // Applicant
            'applicant_purpose'           => $this->applicant_purpose,
            'applicant_occupation'        => $this->applicant_occupation,
            'applicant_company_name'      => $this->applicant_company_name,
            'applicant_company_address'   => $this->applicant_company_address,
            'applicant_monthly_income'    => $this->parseAmount($this->applicant_monthly_income),
            'applicant_monthly_expense'   => $this->parseAmount($this->applicant_monthly_expense),
            'applicant_other_income'      => $this->parseAmount($this->applicant_other_income),
            'reason'                      => $this->reason,

            // Collateral
            'collateral_type'             => $this->collateral_type,
            'collateral_description'      => $this->collateral_description,
            'collateral_certificate_no'   => $this->collateral_certificate_no,
            'collateral_value'            => $this->parseAmount($this->collateral_value),
            'collateral_address'          => $this->collateral_address,

            // Guarantor
            'guarantor_name'              => $this->guarantor_name,
            'guarantor_nik'               => $this->guarantor_nik,
            'guarantor_phone'             => $this->guarantor_phone,
            'guarantor_address'           => $this->guarantor_address,
            'guarantor_relation'          => $this->guarantor_relation,

            // Scheduling
            'due_date_cycle'  => \Carbon\Carbon::parse($this->disbursement_date)->format('d'),
            'disbursement_date' => $this->disbursement_date,
            'created_by' => auth()->id(),
        ];
    }

    public function submit(LoanOperationService $service)
    {
        $this->validate();

        $data = $this->loanPayload();

        // 1. Hook to Approval System
        $status = $this->interceptAction('loans.origination', 'Originate', $data);

        $this->logActivity('ORIGINATE_LOAN', "Melakukan Pendaftaran Pinjaman Baru untuk CIF: {$data['cif_id']}");

        if ($status === 'PENDING') {
            session()->flash('success', 'Pengajuan pendaftaran kredit berhasil dikirim ke antrean persetujuan.');
            return redirect()->route('loans.inquiry');
        }

        // 2. Direct Execution if Approvals Disabled
        DB::transaction(function () use ($service, $data) {
            $service->originateLoan($data);
        });

        session()->flash('success', 'Pendaftaran fasilitas kredit berhasil dibuat.');
        return redirect()->route('loans.inquiry');
    }

    public function getSimulasiProperty(LoanOperationService $service)
    {
        if ($this->principal_amount && $this->interest_rate && $this->tenor) {
            $date = $this->disbursement_date ? \Carbon\Carbon::parse($this->disbursement_date) : now();
            return $service->simulateSchedules(
                $this->parseAmount($this->principal_amount),
                (float) $this->interest_rate,
                (int) $this->tenor,
                $this->calculation_method,
                $date,
                (bool) $this->is_diskonto_product
            );
        }
        return [];
    }

    public function render()
    {
        $query = Cif::query();

        if (strlen($this->searchCif) >= 3 && !$this->selectedCif) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchCif . '%')
                    ->orWhere('cif_no', 'like', '%' . $this->searchCif . '%')
                    ->orWhere('nik', 'like', '%' . $this->searchCif . '%');
            });
            $cifs = $query->limit(5)->get();
        } else {
            $cifs = [];
        }

        return view('livewire.loans.origination', [
            'cifResults'   => $cifs,
            'loanProducts' => LoanProduct::where('is_active', true)->get(),
            'insuranceProducts' => \App\Models\InsuranceProduct::with('provider')
                ->where('is_active', true)
                ->get(),
            'branches'     => Branch::where('is_active', true)->get(),
            'marketings'   => MarketingMaster::orderBy('name')->get(),
        ]);
    }
}
