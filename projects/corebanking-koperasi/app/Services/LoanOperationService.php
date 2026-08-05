<?php

namespace App\Services;

use App\Models\LoanAccount;
use App\Models\LoanSchedule;
use App\Models\LoanTransaction;
use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use App\Models\Journal;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoanOperationService
{
    public function __construct(
        private readonly JournalService         $journalService,
        private readonly PaymentAllocationEngine $allocationEngine,
        private readonly SettlementEngine        $settlementEngine,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // COLLECTIBILITY — OJK POJK No.40/POJK.03/2019
    // KOL 1: 0 hari | KOL 2: 1-90 | KOL 3: 91-120 | KOL 4: 121-180 | KOL 5: >180
    // ─────────────────────────────────────────────────────────────────────────

    public function recalculateCollectibility(LoanAccount $loan): LoanAccount
    {
        if (!in_array($loan->status, ['ACTIVE', 'NPL'])) {
            return $loan;
        }

        $oldestDue = $loan->schedules()
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->whereDate('due_date', '<=', now()->toDateString())
            ->orderBy('due_date')
            ->first();

        $dpd = $oldestDue
            ? max(0, Carbon::parse($oldestDue->due_date)->diffInDays(now()))
            : 0;

        $kol = $this->allocationEngine->calculateKol((int)$dpd);

        $loan->dpd_days  = $dpd;
        $loan->kol_level = $kol;
        $loan->status    = ($kol >= 3 && $loan->outstandingTotal > 0) ? 'NPL' : 'ACTIVE';

        if ($loan->outstandingTotal <= 0) {
            $loan->status    = 'CLOSED';
            $loan->dpd_days  = 0;
            $loan->kol_level = 1;
        }
        $loan->save();

        return $loan;
    }

    public function recalculateCollectibilityForAll(): int
    {
        $count = 0;
        LoanAccount::whereIn('status', ['ACTIVE', 'NPL'])
            ->chunkById(200, function ($loans) use (&$count) {
                foreach ($loans as $loan) {
                    $this->recalculateCollectibility($loan);
                    $count++;
                }
            });
        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NUMBER GENERATORS
    // ─────────────────────────────────────────────────────────────────────────

    public function generateAccountNo(LoanAccount $loan): string
    {
        $product    = \App\Models\LoanProduct::find($loan->loan_product_id);
        $branch     = \App\Models\Branch::find($loan->branch_id);
        $productCode = $product?->product_code ?? 'XXX';
        $branchCode  = $branch?->branch_code ?? str_pad($loan->branch_id, 3, '0', STR_PAD_LEFT);
        $prefix      = $productCode . $branchCode;

        $latest   = LoanAccount::where('account_no', 'like', $prefix . '%')->orderBy('account_no', 'desc')->first();
        $sequence = $latest ? (intval(substr($latest->account_no, strlen($prefix))) + 1) : 1;

        return $prefix . str_pad($sequence, 11, '0', STR_PAD_LEFT);
    }

    public function generatePkNumber(LoanAccount $loan): string
    {
        $date    = $loan->disbursement_date ? Carbon::parse($loan->disbursement_date) : now();
        $dateStr = $date->format('dmY');
        $latest  = LoanAccount::whereNotNull('pk_number')->orderBy('id', 'desc')->first();
        $seq     = 1;
        if ($latest?->pk_number) {
            $parts = explode('/', $latest->pk_number);
            if (count($parts) === 3) $seq = (int)$parts[1] + 1;
        }
        return sprintf('SIRARA/%014d/%s', $seq, $dateStr);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SCHEDULE SIMULATION
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Hitung nominal bunga diskonto di muka = bunga_bulanan × tenor.
     * Seluruh bunga (semua periode) dibayar di muka saat pencairan.
     * Metode wajib FLAT.
     */
    public function calculateDiskontoUpfront(float $principal, float $interestRate, int $tenor): float
    {
        $monthlyInterest = $principal * ($interestRate / 100 / 12);
        return round($monthlyInterest * $tenor, 2);
    }

    /**
     * Generate jadwal angsuran diskonto:
     *   - Periode 1 s/d (tenor-1) : pokok = 0, bunga = flat bulanan, total = bunga
     *   - Periode terakhir (tenor) : pokok = full principal, bunga = flat bulanan, total = pokok + bunga
     *
     * Seluruh bunga (tenor bulan) sudah dipotong di muka saat pencairan.
     * Jadwal tetap menampilkan bunga per bulan sebagai tracking kewajiban.
     * Di akhir tenor, nasabah hanya mengembalikan pokok.
     */
    private function simulateDiskontoSchedules(
        float $principal,
        float $interestRate,
        int $tenor,
        ?Carbon $startDate = null
    ): array {
        $startDate       = $startDate instanceof Carbon ? $startDate : ($startDate ? Carbon::parse($startDate) : now());
        $monthlyInterest = round($principal * ($interestRate / 100 / 12), 2);
        $schedules       = [];

        for ($i = 1; $i <= $tenor; $i++) {
            $dueDate = $startDate->copy()->addMonthsNoOverflow($i);
            $isLast  = ($i === $tenor);

            $schedules[] = [
                'installment_number' => $i,
                'due_date'           => $dueDate->format('Y-m-d'),
                'principal_amount'   => $isLast ? round($principal, 2) : 0,
                'interest_amount'    => $monthlyInterest,
                'balance'            => $isLast ? 0 : round($principal, 2),
                'total_amount'       => $isLast
                    ? round($principal + $monthlyInterest, 2)
                    : $monthlyInterest,
            ];
        }

        return $schedules;
    }

    public function simulateSchedules(
        float $principal,
        float $interestRate,
        int $tenor,
        string $method = 'FLAT',
        ?Carbon $startDate = null,
        bool $isDiskonto = false
    ): array {
        // ── Mode Diskonto — delegate ke simulasi khusus ──────────────────────
        if ($isDiskonto) {
            return $this->simulateDiskontoSchedules($principal, $interestRate, $tenor, $startDate);
        }

        $startDate   = $startDate instanceof Carbon ? $startDate : ($startDate ? Carbon::parse($startDate) : now());
        $monthlyRate = $interestRate / 100 / 12;
        $balance     = $principal;
        $schedules   = [];

        for ($i = 1; $i <= $tenor; $i++) {
            $dueDate = $startDate->copy()->addMonthsNoOverflow($i);

            if ($method === 'FLAT') {
                $principalPmt = $principal / $tenor;
                $interestPmt  = $principal * $monthlyRate;
            } elseif ($method === 'EFFECTIVE') {
                $principalPmt = $principal / $tenor;
                $interestPmt  = $balance * $monthlyRate;
            } else { // ANNUITY
                if ($monthlyRate > 0) {
                    $installment  = $principal * $monthlyRate / (1 - pow(1 + $monthlyRate, -$tenor));
                    $interestPmt  = $balance * $monthlyRate;
                    $principalPmt = $installment - $interestPmt;
                } else {
                    $principalPmt = $principal / $tenor;
                    $interestPmt  = 0;
                }
            }

            $balance -= $principalPmt;
            $schedules[] = [
                'installment_number' => $i,
                'due_date'           => $dueDate->format('Y-m-d'),
                'principal_amount'   => round($principalPmt, 2),
                'interest_amount'    => round($interestPmt, 2),
                'balance'            => round(max(0, $balance), 2),
                'total_amount'       => round($principalPmt + $interestPmt, 2),
            ];
        }

        return $schedules;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ORIGINATE LOAN
    // ─────────────────────────────────────────────────────────────────────────

    public function originateLoan(array $data): LoanAccount
    {
        return DB::transaction(function () use ($data) {
            $loan = new LoanAccount([
                'cif_id'             => $data['cif_id'],
                'loan_product_id'    => $data['loan_product_id'],
                'saving_account_id'  => $data['saving_account_id'],
                'branch_id'          => $data['branch_id'],
                'marketing_id'       => $data['marketing_id'] ?? null,
                'principal_amount'   => $data['principal_amount'],
                'interest_margin'    => $data['interest_margin'] ?? 0,
                'tenor'              => $data['tenor'],
                'tenor_type'         => $data['tenor_type'] ?? 'MONTHS',
                'interest_rate'      => $data['interest_rate'],
                'calculation_method' => $data['calculation_method'],
                'due_date_cycle'     => $data['due_date_cycle'] ?? date('d'),
                'collateral_type'    => $data['collateral_type'],
                'reason'             => $data['reason'] ?? null,
                'disbursement_date'  => $data['disbursement_date'] ?? null,
                'status'             => 'APPROVED',
                'created_by'         => $data['created_by'] ?? Auth::id(),
                'updated_by'         => Auth::id(),
                'approved_by'        => Auth::id(),
                'approved_at'        => now(),
                'applicant_purpose'  => $data['applicant_purpose'] ?? null,
                'applicant_occupation' => $data['applicant_occupation'] ?? null,
                'applicant_company_name' => $data['applicant_company_name'] ?? null,
                'applicant_company_address' => $data['applicant_company_address'] ?? null,
                'applicant_monthly_income'  => $data['applicant_monthly_income'] ?? 0,
                'applicant_monthly_expense' => $data['applicant_monthly_expense'] ?? 0,
                'collateral_certificate_no' => $data['collateral_certificate_no'] ?? null,
                'collateral_value'          => $data['collateral_value'] ?? 0,
                'collateral_address'        => $data['collateral_address'] ?? null,
                'collateral_description'    => $data['collateral_description'] ?? null,
                'guarantor_name'            => $data['guarantor_name'] ?? null,
                'guarantor_nik'             => $data['guarantor_nik'] ?? null,
                'guarantor_phone'           => $data['guarantor_phone'] ?? null,
                'guarantor_address'         => $data['guarantor_address'] ?? null,
                'guarantor_relation'        => $data['guarantor_relation'] ?? null,
                'insurance_product_id'      => $data['insurance_product_id'] ?? null,
                'insurance_rate'            => $data['insurance_rate'] ?? 0,
                'provision_fee'             => $data['provision_fee'] ?? 0,
                'admin_fee'                 => $data['admin_fee'] ?? 0,
                'insurance_fee'             => $data['insurance_fee'] ?? 0,
                'flagging_fee'              => $data['flagging_fee'] ?? 0,
                'stamp_duty_fee'            => $data['stamp_duty_fee'] ?? 0,
                'prepaid_installment_count'  => $data['prepaid_installment_count'] ?? 0,
                'prepaid_installment_amount' => $data['prepaid_installment_amount'] ?? 0,
                'blocked_savings_count'      => $data['blocked_savings_count'] ?? 0,
                'blocked_savings_amount'     => $data['blocked_savings_amount'] ?? 0,
                'analyst_notes'             => $data['analyst_notes'] ?? null,
                'analyst_recommendation'    => $data['analyst_recommendation'] ?? null,
            ]);

            $loan->account_no = $this->generateAccountNo($loan);
            $loan->pk_number  = $this->generatePkNumber($loan);

            // ── Diskonto: hitung bunga di muka sebelum generate jadwal ───────
            $isDiskonto = (bool)($data['is_diskonto'] ?? false);
            $loan->is_diskonto = $isDiskonto;
            if ($isDiskonto) {
                $loan->diskonto_upfront_amount = $this->calculateDiskontoUpfront(
                    (float) $loan->principal_amount,
                    (float) $loan->interest_rate,
                    (int)   $loan->tenor
                );
                // Force FLAT untuk diskonto
                $loan->calculation_method = 'FLAT';
            }

            $loan->save();

            // Generate schedules upfront so they are visible in Inquiry/Approval
            $schedules = $this->simulateSchedules(
                (float) $loan->principal_amount,
                (float) $loan->interest_rate,
                (int)   $loan->tenor,
                $loan->calculation_method,
                $loan->disbursement_date ? Carbon::parse($loan->disbursement_date) : now(),
                $isDiskonto
            );

            foreach ($schedules as $sched) {
                LoanSchedule::create([
                    'loan_account_id'    => $loan->id,
                    'installment_number' => $sched['installment_number'],
                    'due_date'           => $sched['due_date'],
                    'principal_amount'   => $sched['principal_amount'],
                    'interest_amount'    => $sched['interest_amount'],
                    'status'             => 'UNPAID',
                    'created_by'         => $data['created_by'] ?? Auth::id(),
                    'updated_by'         => Auth::id(),
                ]);
            }

            // ── Recalculate prepaid & blocked amounts from ACTUAL schedule rows ──
            // This eliminates any floating-point mismatch between the UI preview
            // simulation and the actual generated schedule data.
            $prepaidCount  = (int)($loan->prepaid_installment_count ?? 0);
            $blockedCount  = (int)($loan->blocked_savings_count ?? 0);

            if ($prepaidCount > 0 || $blockedCount > 0) {
                $actualSchedules = $loan->schedules()->orderBy('installment_number')->get();

                // Prepaid: sum of first N installments (principal + interest)
                if ($prepaidCount > 0) {
                    $prepaidRows = $actualSchedules->take($prepaidCount);
                    $loan->prepaid_installment_amount = round(
                        $prepaidRows->sum('principal_amount') + $prepaidRows->sum('interest_amount'),
                        2
                    );
                }

                // Blocked savings: based on installment N+1 onward or same as first installment
                // Convention: blocked = first installment total × blocked_count
                if ($blockedCount > 0) {
                    $firstInstallment = $actualSchedules->first();
                    $oneInstallment   = round(
                        (float)($firstInstallment->principal_amount ?? 0) + (float)($firstInstallment->interest_amount ?? 0),
                        2
                    );
                    $loan->blocked_savings_amount = round($oneInstallment * $blockedCount, 2);
                }

                $loan->save();
            }

            return $loan;
        });
    }

    public function updateUndisbursedLoan(LoanAccount $loan, array $data): LoanAccount
    {
        return DB::transaction(function () use ($loan, $data) {
            if (!in_array($loan->status, ['PENDING', 'APPROVED'], true)) {
                throw new \Exception('Pinjaman sudah dicairkan atau tidak dapat diedit.');
            }

            if ($loan->transactions()->where('transaction_type', 'DISBURSEMENT')->exists()) {
                throw new \Exception('Pinjaman sudah memiliki transaksi pencairan, tidak dapat diedit.');
            }

            $isDiskonto = (bool)($data['is_diskonto'] ?? false);
            $loan->fill([
                'cif_id'             => $data['cif_id'],
                'loan_product_id'    => $data['loan_product_id'],
                'saving_account_id'  => $data['saving_account_id'],
                'branch_id'          => $data['branch_id'],
                'marketing_id'       => $data['marketing_id'] ?? null,
                'principal_amount'   => $data['principal_amount'],
                'interest_margin'    => $data['interest_margin'] ?? 0,
                'tenor'              => $data['tenor'],
                'tenor_type'         => $data['tenor_type'] ?? 'MONTHS',
                'interest_rate'      => $data['interest_rate'],
                'calculation_method' => $isDiskonto ? 'FLAT' : $data['calculation_method'],
                'due_date_cycle'     => $data['due_date_cycle'] ?? date('d'),
                'collateral_type'    => $data['collateral_type'],
                'reason'             => $data['reason'] ?? null,
                'disbursement_date'  => $data['disbursement_date'] ?? null,
                'updated_by'         => Auth::id(),
                'applicant_purpose'  => $data['applicant_purpose'] ?? null,
                'applicant_occupation' => $data['applicant_occupation'] ?? null,
                'applicant_company_name' => $data['applicant_company_name'] ?? null,
                'applicant_company_address' => $data['applicant_company_address'] ?? null,
                'applicant_monthly_income'  => $data['applicant_monthly_income'] ?? 0,
                'applicant_monthly_expense' => $data['applicant_monthly_expense'] ?? 0,
                'applicant_other_income'    => $data['applicant_other_income'] ?? 0,
                'collateral_certificate_no' => $data['collateral_certificate_no'] ?? null,
                'collateral_value'          => $data['collateral_value'] ?? 0,
                'collateral_address'        => $data['collateral_address'] ?? null,
                'collateral_description'    => $data['collateral_description'] ?? null,
                'guarantor_name'            => $data['guarantor_name'] ?? null,
                'guarantor_nik'             => $data['guarantor_nik'] ?? null,
                'guarantor_phone'           => $data['guarantor_phone'] ?? null,
                'guarantor_address'         => $data['guarantor_address'] ?? null,
                'guarantor_relation'        => $data['guarantor_relation'] ?? null,
                'insurance_product_id'      => $data['insurance_product_id'] ?? null,
                'insurance_rate'            => $data['insurance_rate'] ?? 0,
                'provision_fee'             => $data['provision_fee'] ?? 0,
                'admin_fee'                 => $data['admin_fee'] ?? 0,
                'insurance_fee'             => $data['insurance_fee'] ?? 0,
                'flagging_fee'              => $data['flagging_fee'] ?? 0,
                'stamp_duty_fee'            => $data['stamp_duty_fee'] ?? 0,
                'prepaid_installment_count'  => $data['prepaid_installment_count'] ?? 0,
                'prepaid_installment_amount' => $data['prepaid_installment_amount'] ?? 0,
                'blocked_savings_count'      => $data['blocked_savings_count'] ?? 0,
                'blocked_savings_amount'     => $data['blocked_savings_amount'] ?? 0,
                'analyst_notes'             => $data['analyst_notes'] ?? null,
                'analyst_recommendation'    => $data['analyst_recommendation'] ?? null,
                'is_diskonto'               => $isDiskonto,
                'diskonto_upfront_amount'   => $isDiskonto
                    ? $this->calculateDiskontoUpfront((float) $data['principal_amount'], (float) $data['interest_rate'], (int) $data['tenor'])
                    : 0,
            ]);

            $loan->save();
            $loan->schedules()->delete();

            foreach ($this->simulateSchedules(
                (float) $loan->principal_amount,
                (float) $loan->interest_rate,
                (int) $loan->tenor,
                $loan->calculation_method,
                $loan->disbursement_date ? Carbon::parse($loan->disbursement_date) : now(),
                (bool) $loan->is_diskonto
            ) as $sched) {
                LoanSchedule::create([
                    'loan_account_id'    => $loan->id,
                    'installment_number' => $sched['installment_number'],
                    'due_date'           => $sched['due_date'],
                    'principal_amount'   => $sched['principal_amount'],
                    'interest_amount'    => $sched['interest_amount'],
                    'status'             => 'UNPAID',
                    'created_by'         => $loan->created_by ?? Auth::id(),
                    'updated_by'         => Auth::id(),
                ]);
            }

            return $loan;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DISBURSE LOAN
    // Pencairan kredit dapat ke: INTERNAL (savings account), ABA (bank transfer), CASH
    // ─────────────────────────────────────────────────────────────────────────

    public function disburseLoan(LoanAccount $loan, string $channel = 'INTERNAL', ?int $coaOverrideId = null, ?int $createdBy = null): LoanAccount
    {
        return DB::transaction(function () use ($loan, $channel, $coaOverrideId, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            if (!in_array($loan->status, ['PENDING', 'APPROVED'])) {
                throw new \Exception('Pinjaman tidak dalam status yang dapat dicairkan.');
            }

            $channel = SettlementEngine::normalizeChannel($channel);

            // For INTERNAL, savings account is required
            if ($channel === SettlementEngine::CHANNEL_INTERNAL) {
                if (!$loan->saving_account_id || !$loan->savingAccount) {
                    throw new \Exception(
                        'Pencairan INTERNAL memerlukan rekening tabungan internal nasabah. '
                            . 'Pastikan nasabah memiliki rekening tabungan yang terdaftar.'
                    );
                }
            }

            $product        = $loan->product;
            $savingAccount  = $loan->savingAccount;
            $savingProduct  = $savingAccount?->product;

            // Validate COAs based on channel
            if ($channel === SettlementEngine::CHANNEL_INTERNAL && !$savingProduct?->liability_coa_id) {
                throw new \Exception('COA kewajiban produk tabungan nasabah belum diatur.');
            }

            $loan->status           = 'ACTIVE';
            $loan->disbursement_date = $loan->disbursement_date ?? now();
            $loan->approved_by      = Auth::id() ?? $loan->approved_by;
            $loan->approved_at      = now();

            $loan->outstanding_principal = $loan->principal_amount;
            $totalInterest = $loan->schedules()->sum('interest_amount');
            $diskontoUpfrontAmt = round((float)($loan->diskonto_upfront_amount ?? 0), 2);

            $loan->outstanding_interest  = $totalInterest - $diskontoUpfrontAmt;
            $loan->outstanding_penalty   = 0;
            $loan->dpd_days              = 0;
            $loan->kol_level             = 1;
            $loan->save();

            // Schedules are already generated during origination
            $schedules = $loan->schedules()->orderBy('installment_number')->get();

            $provisionAmt  = round((float)($loan->provision_fee  ?? 0), 2);
            $adminFeeAmt   = round((float)($loan->admin_fee       ?? 0), 2);
            $insuranceAmt  = round((float)($loan->insurance_fee  ?? 0), 2);
            $flaggingAmt   = round((float)($loan->flagging_fee   ?? 0), 2);
            $stampDutyAmt  = round((float)($loan->stamp_duty_fee ?? 0), 2);
            $blockedAmt    = round((float)($loan->blocked_savings_amount ?? 0), 2);

            // ── Diskonto upfront interest deduction ───────────────────────────
            $diskontoUpfrontAmt = round((float)($loan->diskonto_upfront_amount ?? 0), 2);
            if ($loan->is_diskonto && $diskontoUpfrontAmt > 0) {
                foreach ($schedules as $sched) {
                    $sched->interest_paid = $sched->interest_amount;
                    if ($sched->principal_amount <= 0) {
                        $sched->status = 'PAID';
                    } else {
                        $sched->status = 'PARTIAL';
                    }
                    $sched->save();
                }
            }

            // Calculate prepaid totals from schedules to ensure journal balance
            $totalPrepaidPrincipal = 0;
            $totalPrepaidInterest  = 0;
            $prepaidSchedules = collect();

            if ($loan->prepaid_installment_count > 0) {
                $prepaidSchedules = $loan->schedules()
                    ->orderBy('installment_number')
                    ->limit($loan->prepaid_installment_count)
                    ->get();
                foreach ($prepaidSchedules as $pSched) {
                    $totalPrepaidPrincipal += round((float)$pSched->principal_amount, 2);
                    $totalPrepaidInterest  += round((float)$pSched->interest_amount, 2);
                }
            }
            $actualPrepaidAmt = round($totalPrepaidPrincipal + $totalPrepaidInterest, 2);

            // Calculate Net Disbursed using the actual sum from schedules
            $netDisbursed = round(
                $loan->principal_amount
                    - $provisionAmt - $adminFeeAmt - $insuranceAmt
                    - $flaggingAmt  - $stampDutyAmt
                    - $actualPrepaidAmt - $blockedAmt
                    - $diskontoUpfrontAmt,
                2
            );

            // Build journal entries based on channel
            $entries = [
                ['coa_id' => $product->principal_coa_id, 'debit' => $loan->principal_amount, 'credit' => 0],
            ];

            // 1. Credit Net Disbursed amount to the appropriate settlement channel
            if ($channel === SettlementEngine::CHANNEL_INTERNAL) {
                // Dr: Piutang Kredit | Cr: Simpanan Tabungan Nasabah (Net + Blocked)
                // We combine net and blocked here because both go to the same account
                $entries[] = ['coa_id' => $savingProduct->liability_coa_id, 'debit' => 0, 'credit' => $netDisbursed];
            } else {
                // Dr: Piutang Kredit | Cr: Kas/ABA (settlement)
                $settlementCoaId = $this->settlementEngine->resolveForLoan($product, $channel, $coaOverrideId);
                $entries[] = ['coa_id' => $settlementCoaId, 'debit' => 0, 'credit' => $netDisbursed];
            }

            // 2. Credit Blocked Amount to Savings Account Liability COA
            if ($blockedAmt > 0) {
                $entries[] = ['coa_id' => $savingProduct->liability_coa_id, 'debit' => 0, 'credit' => $blockedAmt];
            }

            // 3. Credit Fee Revenue entries
            if ($provisionAmt > 0) {
                if (!$product->provision_revenue_coa_id) {
                    throw new \Exception('COA pendapatan provisi belum diatur pada produk kredit.');
                }
                $entries[] = ['coa_id' => $product->provision_revenue_coa_id, 'debit' => 0, 'credit' => $provisionAmt];
            }
            if ($adminFeeAmt > 0) {
                if (!$product->admin_fee_revenue_coa_id) {
                    throw new \Exception('COA pendapatan administrasi kredit belum diatur pada produk kredit.');
                }
                $entries[] = ['coa_id' => $product->admin_fee_revenue_coa_id, 'debit' => 0, 'credit' => $adminFeeAmt];
            }
            if ($insuranceAmt > 0) {
                if (!$product->insurance_revenue_coa_id) {
                    throw new \Exception('COA pendapatan asuransi belum diatur pada produk kredit.');
                }
                $entries[] = ['coa_id' => $product->insurance_revenue_coa_id, 'debit' => 0, 'credit' => $insuranceAmt];
            }
            if ($flaggingAmt > 0) {
                if (!$product->flagging_revenue_coa_id) {
                    throw new \Exception('COA pendapatan flagging belum diatur pada produk kredit.');
                }
                $entries[] = ['coa_id' => $product->flagging_revenue_coa_id, 'debit' => 0, 'credit' => $flaggingAmt];
            }
            if ($stampDutyAmt > 0) {
                if (!$product->stamp_duty_payable_coa_id) {
                    throw new \Exception('COA kewajiban materai belum diatur pada produk kredit.');
                }
                $entries[] = ['coa_id' => $product->stamp_duty_payable_coa_id, 'debit' => 0, 'credit' => $stampDutyAmt];
            }

            // 4. Credit Prepaid Installments (Principal & Interest)
            if ($totalPrepaidPrincipal > 0) {
                $entries[] = ['coa_id' => $product->principal_coa_id, 'debit' => 0, 'credit' => $totalPrepaidPrincipal];
            }
            if ($totalPrepaidInterest > 0) {
                $entries[] = ['coa_id' => $product->interest_revenue_coa_id, 'debit' => 0, 'credit' => $totalPrepaidInterest];
            }

            // 5. Credit Diskonto Upfront — bunga dibayar di muka (seluruh tenor)
            if ($diskontoUpfrontAmt > 0) {
                $deferredInterestCoaId = $product->deferred_interest_coa_id;
                if (!$deferredInterestCoaId) {
                    throw new \Exception('COA bunga diterima dimuka belum diatur pada produk kredit diskonto.');
                }
                $entries[] = ['coa_id' => $deferredInterestCoaId, 'debit' => 0, 'credit' => $diskontoUpfrontAmt];
            }

            $channelName = match ($channel) {
                SettlementEngine::CHANNEL_ABA => 'ABA (Bank Transfer)',
                SettlementEngine::CHANNEL_CASH => 'Tunai',
                default => 'Tabungan Internal',
            };

            $journal = $this->journalService->createSystemJournal(
                branchId: $loan->branch_id,
                prefix: 'LDS',
                description: "Pencairan Kredit {$loan->account_no} ({$channelName}) — Net: {$netDisbursed}",
                entries: $entries,
            );

            // Record sub-ledger updates for savings
            if ($channel === SettlementEngine::CHANNEL_INTERNAL || $blockedAmt > 0) {
                if (!$savingAccount) {
                    throw new \Exception('Rekening tabungan nasabah tidak ditemukan untuk pencairan dana/dana mengendap.');
                }

                $amountToSaving = ($channel === SettlementEngine::CHANNEL_INTERNAL) ? ($netDisbursed + $blockedAmt) : $blockedAmt;
                $savingAccount->increment('balance', $amountToSaving);

                if ($blockedAmt > 0) {
                    $savingAccount->increment('blocked_balance', $blockedAmt);
                }

                SavingTransaction::create([
                    'transaction_no'    => 'LDS' . now()->format('YmdHis') . rand(100, 999),
                    'saving_account_id' => $savingAccount->id,
                    'transaction_date'  => now(),
                    'type'              => 'DEPOSIT',
                    'channel'           => $channel,
                    'amount'            => $amountToSaving,
                    'balance_after'     => $savingAccount->fresh()->balance,
                    'journal_id'        => $journal->id,
                    'reference_no'      => $journal->reference_no,
                    'description'       => "Pencairan Kredit {$loan->account_no}" . ($blockedAmt > 0 ? " (Termasuk Dana Mengendap Rp" . number_format($blockedAmt, 2, ',', '.') . ")" : ""),
                    'created_by'        => $createdBy,
                    'approved_by'       => Auth::id(),
                ]);
            }

            // Mark prepaid installments as PAID
            if ($loan->prepaid_installment_count > 0) {
                $prepaidSchedules = $loan->schedules()
                    ->orderBy('installment_number')
                    ->limit($loan->prepaid_installment_count)
                    ->get();

                foreach ($prepaidSchedules as $pSched) {
                    $pSched->update([
                        'status' => 'PAID',
                        'principal_paid' => $pSched->principal_amount,
                        'interest_paid' => $pSched->interest_amount,
                        'payment_date' => now(),
                    ]);

                    $loan->outstanding_principal -= $pSched->principal_amount;
                    $loan->outstanding_interest -= $pSched->interest_amount;
                }
                $loan->save();
            }

            LoanTransaction::create([
                'loan_account_id'   => $loan->id,
                'reference_number'  => $journal->reference_no,
                'transaction_type'  => 'DISBURSEMENT',
                'channel'           => $channel,
                'amount_principal'  => $loan->principal_amount,
                'amount_interest'   => $diskontoUpfrontAmt,
                'amount_provision'  => $provisionAmt,
                'amount_admin_fee'  => $adminFeeAmt,
                'amount_insurance_fee' => $insuranceAmt,
                'total_amount'      => $netDisbursed,
                'journal_id'        => $journal->id,
                'description'       => match ($channel) {
                    SettlementEngine::CHANNEL_ABA => 'Pencairan Dana Kredit → Bank Transfer (ABA)',
                    SettlementEngine::CHANNEL_CASH => 'Pencairan Dana Kredit → Tunai',
                    default => 'Pencairan Dana Kredit → Rekening Tabungan Internal',
                } . ($diskontoUpfrontAmt > 0 ? ' | Bunga di muka Rp ' . number_format($diskontoUpfrontAmt, 2, ',', '.') : ''),
                'created_by'        => $createdBy,
                'updated_by'        => Auth::id(),
            ]);

            app(InsuranceOperationService::class)->createPolicyForLoan($loan);

            return $loan;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROCESS REPAYMENT (manual / auto / settlement)
    // ─────────────────────────────────────────────────────────────────────────

    public function processRepayment(
        LoanAccount $loan,
        float $paymentAmount,
        string $type = 'REPAYMENT_MANUAL',
        string $channel = 'CASH',
        ?int $coaOverrideId = null,
        ?int $createdBy = null
    ): LoanAccount {
        return DB::transaction(function () use ($loan, $paymentAmount, $type, $channel, $coaOverrideId, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            $channel = SettlementEngine::normalizeChannel($channel);

            // Manual/settlement payments may be paid before due date; auto-debit only pays due schedules.
            $allocation = $this->allocationEngine->allocate($loan, $paymentAmount, $type !== 'REPAYMENT_AUTO');

            if ($allocation->appliedAmount <= 0) {
                throw new \Exception('Belum ada angsuran yang jatuh tempo untuk dibayarkan.');
            }

            // Update loan outstanding totals (normalized to avoid tiny decimal residue)
            $loan->outstanding_principal = $this->normalizeMoney(
                (float) $loan->outstanding_principal - (float) $allocation->totalPrincipalPaid
            );
            $loan->outstanding_interest = $this->normalizeMoney(
                (float) $loan->outstanding_interest - (float) $allocation->totalInterestPaid
            );
            $loan->outstanding_penalty = $this->normalizeMoney(
                (float) $loan->outstanding_penalty - (float) $allocation->totalPenaltyPaid
            );

            // Reconcile with schedule-level residuals to avoid drift:
            // if all schedules are effectively paid, outstanding must be zero.
            $this->syncOutstandingFromSchedules($loan);

            $loan->save();

            $this->recalculateCollectibility($loan);

            // Resolve settlement COA
            $product         = $loan->product;
            $settlementCoaId = $this->settlementEngine->resolveForLoan($product, $channel, $coaOverrideId);

            if ($allocation->totalInterestPaid > 0 && !$product->interest_revenue_coa_id) {
                throw new \Exception('COA pendapatan bunga belum diatur pada produk kredit.');
            }
            if ($allocation->totalPenaltyPaid > 0 && !$product->penalty_revenue_coa_id) {
                throw new \Exception('COA pendapatan denda belum diatur pada produk kredit.');
            }

            // Build journal entries
            // Dr: Kas/ABA/Internal
            // Cr: Piutang Pokok, Pendapatan Bunga, Pendapatan Denda
            $entries = [
                [
                    'coa_id' => $settlementCoaId,
                    'reference_no' => $loan->account_no,
                    'description' => "Pembayaran Kredit {$loan->account_no} - Total",
                    'debit' => $allocation->appliedAmount,
                    'credit' => 0,
                ],
            ];

            if ($allocation->totalPrincipalPaid > 0) {
                $entries[] = [
                    'coa_id' => $product->principal_coa_id,
                    'reference_no' => $loan->account_no,
                    'description' => "Pembayaran Kredit {$loan->account_no} - Pokok",
                    'debit' => 0,
                    'credit' => $allocation->totalPrincipalPaid,
                ];
            }
            if ($allocation->totalInterestPaid > 0) {
                $entries[] = [
                    'coa_id' => $product->interest_revenue_coa_id,
                    'reference_no' => $loan->account_no,
                    'description' => "Pembayaran Kredit {$loan->account_no} - Kewajiban Bunga",
                    'debit' => 0,
                    'credit' => $allocation->totalInterestPaid,
                ];
            }
            if ($allocation->totalPenaltyPaid > 0) {
                $entries[] = [
                    'coa_id' => $product->penalty_revenue_coa_id,
                    'reference_no' => $loan->account_no,
                    'description' => "Pembayaran Kredit {$loan->account_no} - Denda",
                    'debit' => 0,
                    'credit' => $allocation->totalPenaltyPaid,
                ];
            }

            $prefix = match ($type) {
                'REPAYMENT_SETTLEMENT' => 'LRS',
                'REPAYMENT_AUTO'       => 'LRA',
                default                => 'LRP',
            };

            $journal = $this->journalService->createSystemJournal(
                branchId: $loan->branch_id,
                prefix: $prefix,
                description: $type === 'REPAYMENT_SETTLEMENT'
                    ? "Pelunasan Kredit {$loan->account_no}"
                    : "Pembayaran Kredit {$loan->account_no}",
                entries: $entries,
            );

            app(CoaMovementService::class)->syncForJournal($journal);

            $transactionDescription = match ($type) {
                'REPAYMENT_AUTO' => 'Pembayaran Otomatis (Auto-Debet)',
                'REPAYMENT_SETTLEMENT' => 'Pelunasan Kredit',
                default => 'Pembayaran Manual',
            } . ' - ' . $this->componentDescription(
                $allocation->totalPrincipalPaid,
                $allocation->totalInterestPaid,
                $allocation->totalPenaltyPaid
            );

            LoanTransaction::create([
                'loan_account_id'   => $loan->id,
                'reference_number'  => $journal->reference_no,
                'transaction_type'  => $type,
                'channel'           => $channel,
                'amount_principal'  => $allocation->totalPrincipalPaid,
                'amount_interest'   => $allocation->totalInterestPaid,
                'amount_penalty'    => $allocation->totalPenaltyPaid,
                'total_amount'      => $allocation->appliedAmount,
                'journal_id'        => $journal->id,
                'description'       => $transactionDescription,
                'created_by' => $createdBy,
                'updated_by' => Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);

            // Ensure all remaining partial/unpaid schedules are finalized when loan is fully closed.
            if ($loan->fresh()->status === 'CLOSED') {
                $loan->schedules()
                    ->whereIn('status', ['UNPAID', 'PARTIAL'])
                    ->get()
                    ->each(function ($sched) {
                        $sched->status = 'PAID';
                        $sched->principal_paid = $sched->principal_amount;
                        $sched->interest_paid = $sched->interest_amount;
                        $sched->penalty_paid = $sched->penalty_amount ?? 0;
                        $sched->save();
                    });
            }

            return $loan;
        });
    }

    private function normalizeMoney(float $value): float
    {
        return max(0.0, round($value, 2));
    }

    private function componentDescription(float $principal, float $interest, float $penalty): string
    {
        return 'Pokok Rp ' . number_format($principal, 2, ',', '.')
            . ' | Kewajiban Bunga Rp ' . number_format($interest, 2, ',', '.')
            . ' | Denda Rp ' . number_format($penalty, 2, ',', '.');
    }

    private function syncOutstandingFromSchedules(LoanAccount $loan): void
    {
        $schedules = $loan->schedules()
            ->where('status', '!=', 'VOID')
            ->get([
                'principal_amount',
                'interest_amount',
                'penalty_amount',
                'principal_paid',
                'interest_paid',
                'penalty_paid',
            ]);

        if ($schedules->isEmpty()) {
            return;
        }

        $principalResidual = 0.0;
        $interestResidual = 0.0;
        $penaltyResidual = 0.0;

        foreach ($schedules as $sched) {
            $principalResidual += max(0.0, round((float) $sched->principal_amount - (float) $sched->principal_paid, 2));
            $interestResidual += max(0.0, round((float) $sched->interest_amount - (float) $sched->interest_paid, 2));
            $penaltyResidual += max(0.0, round((float) ($sched->penalty_amount ?? 0) - (float) ($sched->penalty_paid ?? 0), 2));
        }

        $loan->outstanding_principal = $this->normalizeMoney($principalResidual);
        $loan->outstanding_interest = $this->normalizeMoney($interestResidual);
        $loan->outstanding_penalty = $this->normalizeMoney($penaltyResidual);
    }

    /**
     * Settlement that must debit from linked savings account first,
     * then post loan repayment journal via INTERNAL channel.
     */
    public function processSettlementFromSavings(
        LoanAccount $loan,
        float $paymentAmount,
        ?float $interestAmount = null,
        ?float $penaltyAmount = null,
        ?int $createdBy = null
    ): LoanAccount
    {
        return DB::transaction(function () use ($loan, $paymentAmount, $interestAmount, $penaltyAmount, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            $loan->loadMissing('savingAccount');
            $savingAccount = $loan->savingAccount;

            if (!$savingAccount) {
                throw new \Exception('Rekening tabungan terhubung tidak ditemukan untuk pelunasan.');
            }

            if ($savingAccount->status !== 'ACTIVE') {
                throw new \Exception('Rekening tabungan sumber harus berstatus ACTIVE.');
            }

            $amount = round((float) $paymentAmount, 2);
            $effectiveBalance = round((float) $savingAccount->effective_balance, 2);

            if ($effectiveBalance < $amount) {
                throw new \Exception('Saldo efektif tabungan tidak mencukupi untuk pelunasan.');
            }

            $withdrawalDescription = "Pelunasan Kredit {$loan->account_no}";
            if ($interestAmount !== null || $penaltyAmount !== null) {
                $interest = round((float) ($interestAmount ?? 0), 2);
                $penalty = round((float) ($penaltyAmount ?? 0), 2);
                $principal = round($amount - $interest - $penalty, 2);
                $withdrawalDescription .= ' - ' . $this->componentDescription($principal, $interest, $penalty);
            }

            app(SavingOperationService::class)->withdrawal(
                $savingAccount,
                $amount,
                $withdrawalDescription,
                'INTERNAL',
                null,
                $createdBy
            );

            if ($interestAmount !== null || $penaltyAmount !== null) {
                $interest = round((float) ($interestAmount ?? 0), 2);
                $penalty = round((float) ($penaltyAmount ?? 0), 2);
                $principal = round($amount - $interest - $penalty, 2);

                return $this->processDirectSettlement($loan, $principal, $interest, $penalty, $createdBy);
            }

            return $this->processRepayment($loan, $amount, 'REPAYMENT_SETTLEMENT', 'INTERNAL', null, $createdBy);
        });
    }

    private function processDirectSettlement(
        LoanAccount $loan,
        float $principalAmount,
        float $interestAmount,
        float $penaltyAmount,
        ?int $createdBy = null
    ): LoanAccount {
        return DB::transaction(function () use ($loan, $principalAmount, $interestAmount, $penaltyAmount, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            $loan->refresh()->loadMissing('product');

            $principalAmount = round($principalAmount, 2);
            $interestAmount = round($interestAmount, 2);
            $penaltyAmount = round($penaltyAmount, 2);
            $totalAmount = round($principalAmount + $interestAmount + $penaltyAmount, 2);

            if ($principalAmount !== round((float) $loan->outstanding_principal, 2)) {
                throw new \Exception('Pokok pelunasan harus sama dengan outstanding pokok.');
            }

            $product = $loan->product;
            if (!$product->principal_coa_id) {
                throw new \Exception('COA pokok pinjaman belum diatur pada produk.');
            }
            if ($interestAmount > 0 && !$product->interest_revenue_coa_id) {
                throw new \Exception('COA pendapatan bunga belum diatur pada produk.');
            }
            if ($penaltyAmount > 0 && !$product->penalty_revenue_coa_id) {
                throw new \Exception('COA pendapatan denda belum diatur pada produk.');
            }

            $settlementCoaId = $this->settlementEngine->resolveForLoan($product, SettlementEngine::CHANNEL_INTERNAL);
            $entries = [
                [
                    'coa_id' => $settlementCoaId,
                    'reference_no' => $loan->account_no,
                    'description' => "Pelunasan Kredit {$loan->account_no} - Total",
                    'debit' => $totalAmount,
                    'credit' => 0,
                ],
                [
                    'coa_id' => $product->principal_coa_id,
                    'reference_no' => $loan->account_no,
                    'description' => "Pelunasan Kredit {$loan->account_no} - Pokok",
                    'debit' => 0,
                    'credit' => $principalAmount,
                ],
            ];

            if ($interestAmount > 0) {
                $entries[] = [
                    'coa_id' => $product->interest_revenue_coa_id,
                    'reference_no' => $loan->account_no,
                    'description' => "Pelunasan Kredit {$loan->account_no} - Kewajiban Bunga",
                    'debit' => 0,
                    'credit' => $interestAmount,
                ];
            }
            if ($penaltyAmount > 0) {
                $entries[] = [
                    'coa_id' => $product->penalty_revenue_coa_id,
                    'reference_no' => $loan->account_no,
                    'description' => "Pelunasan Kredit {$loan->account_no} - Denda",
                    'debit' => 0,
                    'credit' => $penaltyAmount,
                ];
            }

            $journal = $this->journalService->createSystemJournal(
                branchId: $loan->branch_id,
                prefix: 'LRS',
                description: "Pelunasan Kredit {$loan->account_no}",
                entries: $entries,
            );

            $loan->update([
                'outstanding_principal' => 0,
                'outstanding_interest' => 0,
                'outstanding_penalty' => 0,
                'status' => 'CLOSED',
                'updated_by' => Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);

            $loan->schedules()
                ->whereIn('status', ['UNPAID', 'PARTIAL'])
                ->get()
                ->each(function ($sched) {
                    $sched->status = 'PAID';
                    $sched->principal_paid = $sched->principal_amount;
                    $sched->interest_paid = $sched->interest_amount;
                    $sched->penalty_paid = $sched->penalty_amount ?? 0;
                    $sched->payment_date = now();
                    $sched->save();
                });

            LoanTransaction::create([
                'loan_account_id'   => $loan->id,
                'reference_number'  => $journal->reference_no,
                'transaction_type'  => 'REPAYMENT_SETTLEMENT',
                'channel'           => SettlementEngine::CHANNEL_INTERNAL,
                'amount_principal'  => $principalAmount,
                'amount_interest'   => $interestAmount,
                'amount_penalty'    => $penaltyAmount,
                'total_amount'      => $totalAmount,
                'journal_id'        => $journal->id,
                'description'       => 'Pelunasan Kredit - ' . $this->componentDescription($principalAmount, $interestAmount, $penaltyAmount),
                'created_by' => $createdBy,
                'updated_by' => Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);

            app(CoaMovementService::class)->syncForJournal($journal);

            return $loan->fresh();
        });
    }

    /**
     * Manual repayment sourced from linked savings account so saving mutation is recorded.
     */
    public function processManualRepaymentFromSavings(LoanAccount $loan, float $paymentAmount, ?int $createdBy = null): LoanAccount
    {
        return DB::transaction(function () use ($loan, $paymentAmount, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            $loan->loadMissing('savingAccount');
            $savingAccount = $loan->savingAccount;

            if (!$savingAccount) {
                throw new \Exception('Rekening tabungan terhubung tidak ditemukan untuk pembayaran angsuran.');
            }

            if ($savingAccount->status !== 'ACTIVE') {
                throw new \Exception('Rekening tabungan sumber harus berstatus ACTIVE.');
            }

            $amount = round((float) $paymentAmount, 2);
            $effectiveBalance = round((float) $savingAccount->effective_balance, 2);

            if ($effectiveBalance < $amount) {
                throw new \Exception('Saldo efektif tabungan tidak mencukupi untuk pembayaran angsuran.');
            }

            app(SavingOperationService::class)->withdrawal(
                $savingAccount,
                $amount,
                "Pembayaran Angsuran Kredit {$loan->account_no}",
                'INTERNAL',
                null,
                $createdBy
            );

            return $this->processRepayment($loan, $amount, 'REPAYMENT_MANUAL', 'INTERNAL', null, $createdBy);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AUTO DEBIT
    // Safely process auto-debit from savings account to loan repayment
    // ─────────────────────────────────────────────────────────────────────────

    public function processAutoDebit(
        SavingAccount $savingAccount,
        ?string $referenceDate = null,
        bool $includeUpcoming = false
    ): void
    {
        $date = $referenceDate
            ? Carbon::parse($referenceDate)->toDateString()
            : now()->toDateString();

        // Prevent concurrent auto-debit processing
        $processingKey = "auto_debit_processing_{$savingAccount->id}_{$date}";
        if (\Illuminate\Support\Facades\Cache::has($processingKey)) {
            return; // Already processing for this account
        }

        try {
            \Illuminate\Support\Facades\Cache::put($processingKey, true, now()->addMinutes(5));

            $loans = LoanAccount::where('saving_account_id', $savingAccount->id)
                ->whereIn('status', ['ACTIVE', 'NPL'])
                ->with('schedules')
                ->get();

            foreach ($loans as $loan) {
                // Get due and upcoming schedules
                $dueSchedules = $loan->schedules()
                    ->whereIn('status', ['UNPAID', 'PARTIAL'])
                    ->whereDate('due_date', '<=', $date)
                    ->orderBy('due_date')
                    ->get();

                if ($dueSchedules->isEmpty() && $includeUpcoming) {
                    $dueSchedules = $loan->schedules()
                        ->whereIn('status', ['UNPAID', 'PARTIAL'])
                        ->orderBy('due_date', 'asc')
                        ->limit(1)
                        ->get();
                }

                if ($dueSchedules->isEmpty()) continue;

                // Calculate total due from all overdue/upcoming schedules
                $totalDue = 0;
                foreach ($dueSchedules as $sched) {
                    $principalDue = (float)$sched->principal_amount - ((float)$sched->principal_paid ?? 0);
                    $interestDue  = (float)$sched->interest_amount - ((float)$sched->interest_paid ?? 0);
                    $penaltyDue   = ((float)($sched->penalty_amount ?? 0)) - ((float)($sched->penalty_paid ?? 0));
                    $totalDue    += max(0, $principalDue + $interestDue + $penaltyDue);
                }

                if ($totalDue <= 0) continue;

                // Check effective balance (considering holds and minimum balance)
                $effectiveBalance = max(0, (float) $savingAccount->effective_balance);
                if ($effectiveBalance < $totalDue) {
                    // Deduct partial if any balance available
                    if ($effectiveBalance <= 0) continue;
                    $deductionAmount = $effectiveBalance;
                } else {
                    $deductionAmount = $totalDue;
                }

                // Execute deduction and repayment
                try {
                    app(SavingOperationService::class)->withdrawal(
                        $savingAccount,
                        $deductionAmount,
                        "Auto-Debit Pembayaran Kredit {$loan->account_no}",
                        'INTERNAL'
                    );

                    $this->processRepayment($loan, $deductionAmount, 'REPAYMENT_AUTO', 'INTERNAL');

                    $savingAccount->refresh();
                } catch (\Exception $e) {
                    // Log but don't stop processing other loans
                    \Illuminate\Support\Facades\Log::warning(
                        "Auto-debit failed for loan {$loan->id}: " . $e->getMessage()
                    );
                    continue;
                }
            }
        } finally {
            \Illuminate\Support\Facades\Cache::forget($processingKey);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REVERSE DISBURSEMENT
    // ─────────────────────────────────────────────────────────────────────────

    public function reverseDisbursement(LoanTransaction $disbursementTx): LoanAccount
    {
        return DB::transaction(function () use ($disbursementTx) {
            $loan = $disbursementTx->loanAccount;

            if ($loan->status !== 'ACTIVE') {
                throw new \Exception('Hanya pinjaman aktif yang dapat dibatalkan.');
            }

            $loan->schedules()->update(['status' => 'VOID']);
            $loan->outstanding_principal = 0;
            $loan->outstanding_interest  = 0;
            $loan->outstanding_penalty   = 0;
            $loan->status                = 'CANCELLED';
            $loan->save();

            $origJournal = $disbursementTx->journal;
            $revJournal  = null;

            if ($origJournal) {
                $revJournal = $this->journalService->reverseJournal(
                    $origJournal,
                    "Batal Cair Kredit {$loan->account_no}",
                    autoApprove: true
                );
            }

            $reversalTx = LoanTransaction::create([
                'loan_account_id'      => $loan->id,
                'reference_number'     => $revJournal?->reference_no ?? $this->journalService->generateReferenceNo('LRR'),
                'transaction_type'     => 'REVERSAL',
                'channel'              => $disbursementTx->channel ?? 'INTERNAL',
                'amount_principal'     => -$disbursementTx->amount_principal,
                'amount_provision'     => -$disbursementTx->amount_provision,
                'amount_admin_fee'     => -$disbursementTx->amount_admin_fee,
                'amount_insurance_fee' => -$disbursementTx->amount_insurance_fee,
                'total_amount'         => -$disbursementTx->total_amount,
                'journal_id'           => $revJournal?->id,
                'description'          => 'Batal Cair (Reversal)',
                'created_by'           => Auth::id() ?? \App\Models\User::getSystemUserId(),
                'updated_by'           => Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);

            if ($loan->savingAccount && $origJournal) {
                $savingTrx = \App\Models\SavingTransaction::where('saving_account_id', $loan->savingAccount->id)
                    ->where('reference_no', $origJournal->reference_no)
                    ->first();

                if ($savingTrx) {
                    app(SavingOperationService::class)->reverseTransaction(
                        $savingTrx,
                        "Batal Cair Kredit {$loan->account_no}"
                    );
                }
            }

            $disbursementTx->reversed_by_transaction_id = $reversalTx->id;
            $disbursementTx->save();

            return $loan;
        });
    }
}
