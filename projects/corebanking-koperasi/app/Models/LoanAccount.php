<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;
use App\Traits\ApprovesActions;
use App\Traits\RoundsDecimalsOnSave;

class LoanAccount extends Model
{
    use LogsActivity, ApprovesActions, RoundsDecimalsOnSave;

    protected $fillable = [
        'account_no',
        'pk_number',
        'cif_id',
        'loan_product_id',
        'saving_account_id',
        'branch_id',
        'marketing_id',
        'insurance_product_id',
        'insurance_rate',
        'principal_amount',
        'interest_margin',
        'provision_fee',
        'admin_fee',
        'insurance_fee',
        'tenor',
        'tenor_type',
        'interest_rate',
        'calculation_method',
        'is_diskonto',
        'diskonto_upfront_amount',
        'collateral_type',
        'collateral_description',
        'collateral_certificate_no',
        'collateral_value',
        'collateral_address',
        'reason',
        'applicant_purpose',
        'applicant_occupation',
        'applicant_company_name',
        'applicant_company_address',
        'applicant_monthly_income',
        'applicant_monthly_expense',
        'applicant_other_income',
        'guarantor_name',
        'guarantor_nik',
        'guarantor_phone',
        'guarantor_address',
        'guarantor_relation',
        'analyst_notes',
        'analyst_recommendation',
        'disbursement_date',
        'due_date_cycle',
        'outstanding_principal',
        'outstanding_interest',
        'outstanding_penalty',
        'dpd_days',
        'kol_level',
        'flagging_fee',
        'stamp_duty_fee',
        'prepaid_installment_count',
        'prepaid_installment_amount',
        'blocked_savings_count',
        'blocked_savings_amount',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'principal_amount'         => 'decimal:2',
        'interest_margin'          => 'decimal:2',
        'provision_fee'            => 'decimal:2',
        'admin_fee'                => 'decimal:2',
        'insurance_fee'            => 'decimal:2',
        'insurance_rate'           => 'decimal:2',
        'interest_rate'            => 'decimal:2',
        'collateral_value'         => 'decimal:2',
        'applicant_monthly_income' => 'decimal:2',
        'applicant_monthly_expense'=> 'decimal:2',
        'applicant_other_income'   => 'decimal:2',
        'flagging_fee'             => 'decimal:2',
        'stamp_duty_fee'           => 'decimal:2',
        'prepaid_installment_amount' => 'decimal:2',
        'blocked_savings_amount'   => 'decimal:2',
        'prepaid_installment_count' => 'integer',
        'blocked_savings_count'    => 'integer',
        'outstanding_principal'    => 'decimal:2',
        'outstanding_interest'     => 'decimal:2',
        'outstanding_penalty'      => 'decimal:2',
        'dpd_days'                 => 'integer',
        'kol_level'                => 'integer',
        'is_diskonto'              => 'boolean',
        'diskonto_upfront_amount'  => 'decimal:2',
        'tenor'                    => 'integer',
        'due_date_cycle'           => 'integer',
        'disbursement_date'        => 'date',
        'approved_at'              => 'datetime',
    ];

    public function getOutstandingTotalAttribute()
    {
        return $this->outstanding_principal + $this->outstanding_interest + $this->outstanding_penalty;
    }

    public function cif(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cif::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }

    public function marketing(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\MarketingMaster::class, 'marketing_id');
    }

    public function insuranceProduct(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InsuranceProduct::class, 'insurance_product_id');
    }

    public function savingAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SavingAccount::class, 'saving_account_id');
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function schedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoanSchedule::class)->orderBy('installment_number');
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoanTransaction::class)->orderBy('created_at', 'desc');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoanDocument::class)->orderBy('created_at', 'desc');
    }

    public function insurancePolicies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoanInsurancePolicy::class)->orderBy('created_at', 'desc');
    }

    public function insuranceClaims(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InsuranceClaim::class)->orderBy('created_at', 'desc');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
