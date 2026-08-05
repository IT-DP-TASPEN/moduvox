<?php

namespace App\Services;

use App\Models\DepositAccount;
use App\Models\DepositBilyet;
use App\Models\DepositSchedule;
use App\Models\DepositTransaction;
use App\Models\SavingAccount;
use App\Services\CoaMovementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * DepositOperationService — Refactored
 *
 * Aturan journal deposito:
 *
 *  Penempatan Tunai:
 *    Dr: 110100 (Kas)             Cr: 212000 (Simpanan Berjangka)
 *
 *  Penempatan ABA:
 *    Dr: 110300 (Giro ABA)        Cr: 212000 (Simpanan Berjangka)
 *
 *  Pencairan Bunga (WAJIB → Rekening Tabungan Internal):
 *    1) Dr: 511000 (Beban Bunga Simpanan Berjangka)  Cr: 213000 (Hutang Bunga Simpanan Berjangka)
 *    2) Dr: 213000 (Hutang Bunga Simpanan Berjangka) Cr: 211000 (Simpanan Tabungan Nasabah)
 *
 *  Penarikan Pokok Deposito:
 *    Dr: 212000 (Simpanan Berjangka) Cr: 110100/110300 (Kas/ABA)
 */
class DepositOperationService
{
    public function __construct(
        private readonly JournalService        $journalService,
        private readonly SettlementEngine      $settlementEngine,
        private readonly SavingOperationService $savingService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // ACCOUNT NO GENERATOR
    // ─────────────────────────────────────────────────────────────────────────

    public function generateAccountNo(int $branchId, int $productId): string
    {
        $product    = \App\Models\DepositProduct::findOrFail($productId);
        $branch     = \App\Models\Branch::findOrFail($branchId);
        $branchCode = $branch->branch_code ?? str_pad($branchId, 3, '0', STR_PAD_LEFT);
        $prefix     = $product->product_code . $branchCode;

        $latest   = DepositAccount::where('account_no', 'like', $prefix . '%')
            ->orderBy('account_no', 'desc')->first();
        $sequence = $latest ? (intval(substr($latest->account_no, strlen($prefix))) + 1) : 1;

        return $prefix . str_pad($sequence, 11, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INTEREST SIMULATION
    // ─────────────────────────────────────────────────────────────────────────

    public function calculateSimulation(
        float $amount,
        int $productId,
        int $tenor,
        ?float $interestRate = null,
        string $calculationType = 'MONTHLY',
        \Carbon\CarbonInterface|string|null $placementDate = null,
        ?float $taxRate = null
    ): array {
        $product        = \App\Models\DepositProduct::findOrFail($productId);
        $rate           = $interestRate ?? $product->max_interest_rate;
        $placementDate  = $placementDate instanceof \Carbon\CarbonInterface
            ? $placementDate->copy()
            : ($placementDate ? \Carbon\Carbon::parse($placementDate) : now());
        $maturityDate   = $this->depositMaturityDate($placementDate, $product, $tenor);
        $taxRateDec     = (($taxRate ?? (float) ($product->tax_rate ?? 0)) / 100);
        $schedule       = [];
        $totalGross = $totalTax = $totalNet = 0;

        if ($product->interest_period === 'MATURITY') {
            $days = $placementDate->diffInDays($maturityDate);
            $gross = ($calculationType === 'DAILY' || $this->depositTermIsDays($product))
                ? ($amount * ($rate / 100)) / 360 * $days
                : ($amount * ($rate / 100)) / 12 * $tenor;
            $tax = $gross * $taxRateDec;
            $net = $gross - $tax;

            return [
                'principal'        => (float) $amount,
                'tenor'            => $tenor,
                'rate'             => (float) $rate,
                'calculation_type' => $calculationType,
                'gross_interest'   => $gross,
                'tax_amount'       => $tax,
                'net_interest'     => $net,
                'total_payout'     => $amount + $net,
                'placement_date'   => $placementDate->format('Y-m-d'),
                'maturity_date'    => $maturityDate->format('Y-m-d'),
                'schedule'         => [[
                    'month'          => 1,
                    'date'           => $maturityDate->format('Y-m-d'),
                    'days'           => $days,
                    'gross_interest' => $gross,
                    'tax'            => $tax,
                    'net_interest'   => $net,
                ]],
            ];
        }

        for ($i = 1; $i <= $tenor; $i++) {
            $periodStart = $this->depositTermIsDays($product)
                ? $placementDate->copy()->addDays($i - 1)
                : $placementDate->copy()->addMonthsNoOverflow($i - 1);
            $payoutDate  = $this->depositTermIsDays($product)
                ? $placementDate->copy()->addDays($i)
                : $placementDate->copy()->addMonthsNoOverflow($i);
            $daysInMonth = $periodStart->diffInDays($payoutDate);

            $gross = ($calculationType === 'DAILY' || $this->depositTermIsDays($product))
                ? ($amount * ($rate / 100)) / 360 * $daysInMonth
                : ($amount * ($rate / 100)) / 12;

            $tax = $gross * $taxRateDec;
            $net = $gross - $tax;

            $totalGross += $gross;
            $totalTax += $tax;
            $totalNet += $net;

            $schedule[] = [
                'month'          => $i,
                'date'           => $payoutDate->format('Y-m-d'),
                'days'           => $daysInMonth,
                'gross_interest' => $gross,
                'tax'            => $tax,
                'net_interest'   => $net,
            ];
        }

        return [
            'principal'        => (float) $amount,
            'tenor'            => $tenor,
            'rate'             => (float) $rate,
            'calculation_type' => $calculationType,
            'gross_interest'   => $totalGross,
            'tax_amount'       => $totalTax,
            'net_interest'     => $totalNet,
            'total_payout'     => $amount + $totalNet,
            'placement_date'   => $placementDate->format('Y-m-d'),
            'maturity_date'    => $maturityDate->format('Y-m-d'),
            'schedule'         => $schedule,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OPEN ACCOUNT (PENEMPATAN)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Buka rekening deposito baru.
     *
     * @param  array  $data  Must include deposit_channel: CASH | ABA
     */
    public function openAccount(array $data): DepositAccount
    {
        return DB::transaction(function () use ($data) {
            $product   = \App\Models\DepositProduct::findOrFail($data['deposit_product_id']);
            $this->assertProductRules($product, $data);
            $accountNo = $this->generateAccountNo($data['branch_id'], $data['deposit_product_id']);
            $channel   = SettlementEngine::normalizeChannel($data['deposit_channel'] ?? 'CASH');
            $sourceSavingAccount = null;

            if (SettlementEngine::isInternal($channel)) {
                $sourceSavingAccount = SavingAccount::with('product')->findOrFail($data['source_saving_account_id'] ?? null);
                if ((int) $sourceSavingAccount->cif_id !== (int) $data['cif_id']) {
                    throw new \Exception('Rekening simpanan sumber dana harus milik CIF yang sama.');
                }
                if ($sourceSavingAccount->status !== 'ACTIVE') {
                    throw new \Exception('Rekening simpanan sumber dana harus berstatus ACTIVE.');
                }
                if ((float) $sourceSavingAccount->effective_balance < (float) $data['amount']) {
                    throw new \Exception('Saldo efektif rekening simpanan tidak mencukupi untuk penempatan.');
                }
            }

            // Mark bilyet as USED if provided
            if (!empty($data['deposit_bilyet_id'])) {
                $bilyet = DepositBilyet::whereKey($data['deposit_bilyet_id'])->lockForUpdate()->firstOrFail();
                if ($bilyet->status !== 'AVAILABLE') {
                    throw new \Exception('Bilyet tidak tersedia atau sudah digunakan.');
                }
                $bilyet->update(['status' => 'USED']);
            }

            $account = DepositAccount::create([
                'account_no'                => $accountNo,
                'cif_id'                    => $data['cif_id'],
                'deposit_product_id'        => $data['deposit_product_id'],
                'deposit_bilyet_id'         => $data['deposit_bilyet_id'] ?? null,
                'amount'                    => $data['amount'],
                'reason'                    => $data['reason'] ?? null,
                'interest_rate'             => $data['interest_rate'] ?? $product->max_interest_rate,
                'tenor'                     => $data['tenor'],
                'interest_calculation_type' => $data['interest_calculation_type'] ?? 'MONTHLY',
                'placement_date'            => $data['placement_date'] ?? now(),
                'maturity_date'             => $this->depositMaturityDate(
                    \Carbon\Carbon::parse($data['placement_date'] ?? now()),
                    $product,
                    (int) $data['tenor'],
                ),
                'rollover_type'             => $data['rollover_type'] ?? 'NONE',
                'saving_account_id'         => $data['saving_account_id'] ?? null,
                'branch_id'                 => $data['branch_id'],
                'fund_channel'              => $channel,
                'status'                    => 'ACTIVE',
                'created_by'                => $data['created_by'] ?? Auth::id() ?? \App\Models\User::getSystemUserId(),
                'approved_by'               => Auth::id() ?? \App\Models\User::getSystemUserId(),
                'approved_at'               => now(),
            ]);

            // Post placement journal (dengan override COA jika dipilih user)
            $journal = $this->postPlacementJournal($account, $channel, $data['coa_override_id'] ?? null, $sourceSavingAccount);

            if ($sourceSavingAccount) {
                $sourceSavingAccount->decrement('balance', (float) $account->amount);
                \App\Models\SavingTransaction::create([
                    'transaction_no'    => 'DPI' . now()->format('YmdHis') . rand(100, 999),
                    'saving_account_id' => $sourceSavingAccount->id,
                    'transaction_date'  => now(),
                    'type'              => 'WITHDRAWAL',
                    'channel'           => 'INTERNAL',
                    'amount'            => $account->amount,
                    'balance_after'     => $sourceSavingAccount->fresh()->balance,
                    'journal_id'        => $journal->id,
                    'reference_no'      => $journal->reference_no,
                    'description'       => "Penempatan Simpanan Berjangka {$account->account_no}",
                    'created_by'        => $data['created_by'] ?? Auth::id() ?? \App\Models\User::getSystemUserId(),
                ]);
            }

            DepositTransaction::create([
                'transaction_no'     => 'DPI' . now()->format('YmdHis') . rand(100, 999),
                'deposit_account_id' => $account->id,
                'transaction_date'   => now(),
                'type'               => 'PLACEMENT',
                'channel'            => $channel,
                'amount'             => $account->amount,
                'journal_id'         => $journal->id,
                'reference_no'       => $journal->reference_no,
                'description'        => "Penempatan Simpanan Berjangka — Channel: {$channel}",
                'created_by'         => $data['created_by'] ?? Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);

            app(CoaMovementService::class)->syncForJournal($journal);

            // Generate interest schedules
            $simulation = $this->calculateSimulation(
                $account->amount,
                $account->deposit_product_id,
                $account->tenor,
                $account->interest_rate,
                $account->interest_calculation_type,
                $account->placement_date
            );

            foreach ($simulation['schedule'] as $row) {
                DepositSchedule::create([
                    'deposit_account_id' => $account->id,
                    'month_index'        => $row['month'],
                    'schedule_date'      => $row['date'],
                    'gross_interest'     => $row['gross_interest'],
                    'tax_amount'         => $row['tax'],
                    'net_interest'       => $row['net_interest'],
                    'status'             => 'PENDING',
                ]);
            }

            return $account;
        });
    }

    private function assertProductRules(\App\Models\DepositProduct $product, array $data): void
    {
        $amount = (float) ($data['amount'] ?? 0);
        $tenor = (int) ($data['tenor'] ?? 0);
        $rate = (float) ($data['interest_rate'] ?? $product->max_interest_rate);
        $unit = str_starts_with(strtoupper((string) $product->term_unit), 'DAY') ? 'hari' : 'bulan';

        if (!$product->is_active) {
            throw new \Exception("Produk simpanan berjangka [{$product->name}] tidak aktif.");
        }
        if ($amount < (float) $product->min_amount) {
            throw new \Exception("Minimal penempatan produk [{$product->name}] adalah Rp " . number_format((float) $product->min_amount, 0, ',', '.'));
        }
        if ($product->max_amount && $amount > (float) $product->max_amount) {
            throw new \Exception("Maksimal penempatan produk [{$product->name}] adalah Rp " . number_format((float) $product->max_amount, 0, ',', '.'));
        }
        if ($tenor < (int) $product->min_term) {
            throw new \Exception("Tenor minimal produk [{$product->name}] adalah {$product->min_term} {$unit}.");
        }
        if ($product->max_term && $tenor > (int) $product->max_term) {
            throw new \Exception("Tenor maksimal produk [{$product->name}] adalah {$product->max_term} {$unit}.");
        }
        if ($rate < (float) $product->min_interest_rate || $rate > (float) $product->max_interest_rate) {
            throw new \Exception("Suku bunga produk [{$product->name}] harus di antara {$product->min_interest_rate}% - {$product->max_interest_rate}%.");
        }
    }

    private function depositTermIsDays(\App\Models\DepositProduct $product): bool
    {
        return str_starts_with(strtoupper((string) $product->term_unit), 'DAY');
    }

    private function depositMaturityDate(\Carbon\Carbon $placementDate, \App\Models\DepositProduct $product, int $tenor): \Carbon\Carbon
    {
        return $this->depositTermIsDays($product)
            ? $placementDate->copy()->addDays($tenor)
            : $placementDate->copy()->addMonthsNoOverflow($tenor);
    }

    public function ensureSchedules(DepositAccount $account): void
    {
        if ($account->schedules()->exists()) {
            return;
        }

        $simulation = $this->calculateSimulation(
            (float) $account->amount,
            (int) $account->deposit_product_id,
            (int) $account->tenor,
            (float) $account->interest_rate,
            (string) $account->interest_calculation_type,
            $account->placement_date
        );

        foreach ($simulation['schedule'] as $row) {
            DepositSchedule::create([
                'deposit_account_id' => $account->id,
                'month_index'        => $row['month'],
                'schedule_date'      => $row['date'],
                'gross_interest'     => $row['gross_interest'],
                'tax_amount'         => $row['tax'],
                'net_interest'       => $row['net_interest'],
                'status'             => 'PENDING',
            ]);
        }
    }

    public function ensureRolloverSchedules(DepositAccount $account, \Carbon\CarbonInterface|string|null $date = null): void
    {
        if (!in_array($account->status, ['ACTIVE', 'MATURED'], true)) {
            return;
        }

        if (!in_array(strtoupper($account->rollover_type ?? 'NONE'), ['PRINCIPAL', 'PRINCIPAL_INTEREST'], true)) {
            return;
        }

        $today = $this->businessDate($date);

        while (
            !$account->schedules()->where('status', 'PENDING')->exists()
            && !$account->maturity_date->gt($today)
        ) {
            $this->processMaturity($account);
            $account = $account->fresh(['schedules']);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PENCAIRAN BUNGA DEPOSITO → WAJIB KE REKENING TABUNGAN INTERNAL
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cairkan bunga simpanan berjangka ke rekening tabungan nasabah yang terdaftar.
     *
     * Alur:
     *  1. Dr Beban Bunga Simpanan Berjangka → Cr Hutang Bunga Simpanan Berjangka
     *  2. Dr Hutang Bunga Simpanan Berjangka → Cr Simpanan Tabungan (mutasi tabungan)
     *
     * @param  DepositSchedule $schedule
     * @return void
     * @throws \Exception  Jika tidak ada rekening tabungan yang ditautkan
     */
    public function disbursePeriodInterest(DepositSchedule $schedule, bool $processMaturity = true, ?int $createdBy = null): void
    {
        DB::transaction(function () use ($schedule, $processMaturity, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            $schedule = DepositSchedule::query()
                ->whereKey($schedule->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($schedule->status !== 'PENDING') {
                throw new \Exception('Jadwal bunga ini sudah diproses.');
            }

            $schedule->loadMissing(['account.product', 'account.savingAccount.product']);

            $account = $schedule->account;
            $product = $account->product;

            if (!$account->saving_account_id) {
                throw new \Exception(
                    "Rekening tabungan belum ditautkan pada simpanan berjangka {$account->account_no}. "
                        . "Bunga simpanan berjangka wajib dikreditkan ke rekening tabungan internal nasabah."
                );
            }

            $savingAccount = SavingAccount::findOrFail($account->saving_account_id);
            $grossInterest = (float) $schedule->gross_interest;
            $taxAmount     = (float) $schedule->tax_amount;
            $netInterest   = (float) $schedule->net_interest;

            // Validate COA
            $bebanBungaCoaId  = $product->interest_expense_coa_id
                ?? throw new \Exception('COA Beban Bunga Simpanan Berjangka belum diatur.');
            $hutangBungaCoaId = $product->interest_payable_coa_id
                ?? $product->accrued_interest_payable_coa_id
                ?? throw new \Exception('COA Hutang Bunga Simpanan Berjangka belum diatur.');
            $pajakBungaCoaId  = $product->tax_liability_coa_id
                ?? throw new \Exception('COA Hutang Pajak Bunga Simpanan Berjangka belum diatur.');
            $tabunganCoaId    = $savingAccount->product->liability_coa_id
                ?? throw new \Exception('COA Simpanan Tabungan nasabah belum diatur.');

            // Jurnal 1: Beban Bunga → Hutang Bunga (Accrual)
            $journal1 = $this->journalService->createSystemJournal(
                branchId: $account->branch_id,
                prefix: 'DBB',
                description: "Akrual Bunga Simpanan Berjangka {$account->account_no} Bulan ke-{$schedule->month_index}",
                entries: JournalService::drCr($bebanBungaCoaId, $hutangBungaCoaId, $grossInterest),
            );

            // Jurnal 2: Hutang Bunga → Simpanan Tabungan Nasabah + Hutang Pajak (Pembayaran)
            $journal2 = $this->journalService->createSystemJournal(
                branchId: $account->branch_id,
                prefix: 'DBC',
                description: "Pembayaran Bunga Simpanan Berjangka {$account->account_no} → Tabungan {$savingAccount->account_no}",
                entries: [
                    ['coa_id' => $hutangBungaCoaId, 'debit' => $grossInterest, 'credit' => 0],
                    ['coa_id' => $tabunganCoaId,    'debit' => 0,              'credit' => $netInterest],
                    ['coa_id' => $pajakBungaCoaId,  'debit' => 0,              'credit' => $taxAmount],
                ],
            );

            // Mutasi tabungan nasabah (kredit saldo)
            $savingAccount->increment('balance', $netInterest);
            \App\Models\SavingTransaction::create([
                'transaction_no'    => $this->savingTransactionNo('DBI'),
                'saving_account_id' => $savingAccount->id,
                'transaction_date'  => now(),
                'type'              => 'INTEREST',
                'channel'           => 'INTERNAL',
                'amount'            => $netInterest,
                'balance_after'     => $savingAccount->fresh()->balance,
                'journal_id'        => $journal2->id,
                'reference_no'      => $journal2->reference_no,
                'description'       => "Bunga Simpanan Berjangka {$account->account_no} Bulan ke-{$schedule->month_index}",
                'created_by'        => $createdBy,
            ]);

            // Catat DepositTransaction (INTEREST) dan link ke schedule
            $depositTrx = DepositTransaction::create([
                'transaction_no'     => $this->depositTransactionNo('DBI'),
                'deposit_account_id' => $account->id,
                'transaction_date'   => now(),
                'type'               => 'INTEREST_PAYMENT',
                'channel'            => 'INTERNAL',
                'amount'             => $netInterest,
                'journal_id'         => $journal2->id,
                'reference_no'       => $journal2->reference_no,
                'description'        => "Bunga Simpanan Berjangka Bulan ke-{$schedule->month_index} — {$account->account_no}",
                'created_by'         => $createdBy,
            ]);

            // Mark schedule as PAID dan link ke deposit transaction
            $schedule->update([
                'status'                 => 'PAID',
                'payment_date'           => now(),
                'deposit_transaction_id' => $depositTrx->id,
            ]);

            app(CoaMovementService::class)->syncForJournal($journal1);
            app(CoaMovementService::class)->syncForJournal($journal2);

            if ($processMaturity) {
                $this->processMaturityIfReady($account->fresh());
            }
        });
    }

    private function processMaturityIfReady(DepositAccount $account): void
    {
        if (!in_array($account->status, ['ACTIVE', 'MATURED'], true)) {
            return;
        }

        if ($account->maturity_date->gt($this->businessDate())) {
            return;
        }

        if ($account->schedules()->where('status', 'PENDING')->exists()) {
            return;
        }

        $this->processMaturity($account);
    }

    private function businessDate(\Carbon\CarbonInterface|string|null $date = null): \Carbon\Carbon
    {
        if ($date instanceof \Carbon\CarbonInterface) {
            return \Carbon\Carbon::instance($date->toDateTime())->endOfDay();
        }

        if ($date) {
            return \Carbon\Carbon::parse($date)->endOfDay();
        }

        return config('app.business_date')
            ? \Carbon\Carbon::parse(config('app.business_date'))->endOfDay()
            : now();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLOSE ACCOUNT (PENARIKAN POKOK)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Tutup rekening deposito (pencairan pokok).
     *
     * @param  array  $data  Must include payout_channel: CASH | ABA | INTERNAL
     */
    public function closeAccount(array $data): DepositAccount
    {
        return DB::transaction(function () use ($data) {
            $account = DepositAccount::findOrFail($data['deposit_account_id']);
            $channel = SettlementEngine::normalizeChannel($data['payout_channel'] ?? $data['payout_type'] ?? 'CASH');

            if (!in_array($account->status, ['ACTIVE', 'MATURED'])) {
                throw new \Exception('Status rekening tidak valid untuk penutupan.');
            }

            $product         = $account->product;
            $penalty         = (float)($data['penalty_amount'] ?? 0);
            $principalPayout = $account->amount - $penalty;

            // ── INTERNAL: Payout langsung ke rekening tabungan nasabah ────────
            if (SettlementEngine::isInternal($channel)) {
                // Can use either linked account or explicit target account from request
                $targetAccountId = $data['saving_account_id'] ?? $account->saving_account_id;
                if (!$targetAccountId) {
                    throw new \Exception(
                        "Payout INTERNAL memerlukan rekening tabungan yang ditautkan pada deposito {$account->account_no}."
                    );
                }

                $savingAccount  = SavingAccount::findOrFail($targetAccountId);
                $tabunganCoaId  = $savingAccount->product->liability_coa_id
                    ?? throw new \Exception('COA Simpanan Tabungan nasabah belum diatur.');
                $depositoCoaId  = $product->liability_coa_id
                    ?? throw new \Exception('COA Simpanan Berjangka belum diatur.');

                // Journal: Dr Simpanan Berjangka → Cr Simpanan Tabungan (+ Penalty if applicable)
                $entries = [
                    ['coa_id' => $depositoCoaId, 'debit' => $account->amount, 'credit' => 0],
                    ['coa_id' => $tabunganCoaId, 'debit' => 0,                'credit' => $principalPayout],
                ];

                if ($penalty > 0 && $product->admin_fee_revenue_coa_id) {
                    $entries[] = ['coa_id' => $product->admin_fee_revenue_coa_id, 'debit' => 0, 'credit' => $penalty];
                }

                $journal = $this->journalService->createSystemJournal(
                    branchId: $account->branch_id,
                    prefix: 'DPW',
                    description: "Pencairan Deposito {$account->account_no} → Tabungan {$savingAccount->account_no} (INTERNAL)",
                    entries: $entries,
                );

                // Kredit saldo tabungan nasabah
                $savingAccount->increment('balance', $principalPayout);
                \App\Models\SavingTransaction::create([
                    'transaction_no'    => 'DPW' . now()->format('YmdHis') . rand(100, 999),
                    'saving_account_id' => $savingAccount->id,
                    'transaction_date'  => now(),
                    'type'              => 'DEPOSIT',
                    'channel'           => 'INTERNAL',
                    'amount'            => $principalPayout,
                    'balance_after'     => $savingAccount->fresh()->balance,
                    'journal_id'        => $journal->id,
                    'reference_no'      => $journal->reference_no,
                    'description'       => "Pencairan Pokok Deposito {$account->account_no}",
                    'created_by'        => $data['created_by'] ?? Auth::id() ?? \App\Models\User::getSystemUserId(),
                ]);
            } else {
                // ── CASH / ABA: Payout ke Kas atau Giro ABA ──────────────────
                $assetCoaId    = $this->settlementEngine->resolveForDeposit($product, $channel, $data['coa_override_id'] ?? null);
                $depositoCoaId = $product->liability_coa_id
                    ?? throw new \Exception('COA Simpanan Berjangka belum diatur.');

                // Journal: Dr Simpanan Berjangka → Cr Kas/ABA (+ Penalty if applicable)
                $entries = [
                    ['coa_id' => $depositoCoaId, 'debit' => $account->amount, 'credit' => 0],
                    ['coa_id' => $assetCoaId,    'debit' => 0,                'credit' => $principalPayout],
                ];

                if ($penalty > 0 && $product->admin_fee_revenue_coa_id) {
                    $entries[] = ['coa_id' => $product->admin_fee_revenue_coa_id, 'debit' => 0, 'credit' => $penalty];
                }

                $journal = $this->journalService->createSystemJournal(
                    branchId: $account->branch_id,
                    prefix: 'DPW',
                    description: "Penarikan Deposito {$account->account_no} — Channel: {$channel}",
                    entries: $entries,
                );
            }

            // Update account status
            $account->update(['status' => 'CLOSED', 'closed_at' => now(), 'updated_by' => Auth::id() ?? \App\Models\User::getSystemUserId()]);

            // Mark bilyet
            if ($account->deposit_bilyet_id) {
                $account->bilyet?->update(['status' => 'CANCELLED']);
            }

            // Record deposit transaction
            DepositTransaction::create([
                'transaction_no'     => 'DPW' . now()->format('YmdHis') . rand(100, 999),
                'deposit_account_id' => $account->id,
                'transaction_date'   => now(),
                'type'               => 'WITHDRAWAL',
                'channel'            => $channel,
                'amount'             => $account->amount,
                'journal_id'         => $journal->id,
                'reference_no'       => $journal->reference_no,
                'description'        => "Penutupan Deposito — Channel: {$channel}. Penalty: {$penalty}",
                'created_by'         => $data['created_by'] ?? Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);

            app(CoaMovementService::class)->syncForJournal($journal);

            return $account;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MATURITY PROCESSING (Non-ARO / ARO Pokok / ARO Pokok+Bunga)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Proses deposito yang sudah jatuh tempo.
     *
     * @param  DepositAccount  $account  Deposito ACTIVE yang maturity_date <= today
     * @return string  'CLOSED' | 'ROLLED_OVER'
     */
    public function processMaturity(DepositAccount $account): string
    {
        return DB::transaction(function () use ($account) {
            $account->load(['product', 'savingAccount.product', 'schedules']);

            // ── 1. Bayar semua bunga yang masih PENDING ──────────────────────
            $pendingSchedules = $account->schedules->where('status', 'PENDING');
            $interestToRoll = $pendingSchedules->sum('net_interest');
            foreach ($pendingSchedules as $schedule) {
                $this->disbursePeriodInterest($schedule, false);
            }

            $rolloverType = strtoupper($account->rollover_type ?? 'NONE');

            // ── 2. Non-ARO: Cairkan pokok ke tabungan → tutup ────────────────
            if ($rolloverType === 'NONE') {
                $this->closeAccount([
                    'deposit_account_id' => $account->id,
                    'payout_channel'     => 'INTERNAL',
                    'saving_account_id'  => $account->saving_account_id,
                    'penalty_amount'     => 0,
                ]);

                return 'CLOSED';
            }

            // ── 3. ARO: Perpanjang deposito ──────────────────────────────────
            $product = $account->product;

            // Hitung nominal rollover berdasarkan tipe
            if ($rolloverType === 'PRINCIPAL_INTEREST') {
                // ARO Pokok + Bunga: hanya bunga periode jatuh tempo saat ini yang masuk pokok.
                $newPrincipal = round((float) $account->amount + (float) $interestToRoll, 2);
            } else {
                // ARO Pokok Saja: bunga sudah dicairkan ke tabungan di step 1
                $newPrincipal = (float)$account->amount;
            }

            $lastScheduleDate = $account->schedules()
                ->orderByDesc('schedule_date')
                ->orderByDesc('month_index')
                ->value('schedule_date');
            $newPlacementDate = \Carbon\Carbon::parse($lastScheduleDate ?? $account->maturity_date);
            $newMaturityDate = $newPlacementDate->copy()->addMonthsNoOverflow($account->tenor);

            $account->update([
                'amount' => $newPrincipal,
                'maturity_date' => $newMaturityDate,
                'status' => 'ACTIVE',
                'closed_at' => null,
                'updated_by' => Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);

            // Rollover di rekening yang sama: jurnal hanya diperlukan jika bunga ikut menambah pokok.
            $depositoCoaId = $product->liability_coa_id
                ?? throw new \Exception('COA Simpanan Berjangka belum diatur.');

            if ($rolloverType === 'PRINCIPAL_INTEREST' && (float) $interestToRoll > 0) {
                // Selisih bunga yang dirollover perlu dijurnal:
                // Dr Hutang Bunga → Cr Simpanan Berjangka (untuk menambah saldo deposito)
                $interestRolled = round((float) $interestToRoll, 2);
                $tabunganCoaId  = $account->savingAccount?->product?->liability_coa_id;

                if ($tabunganCoaId) {
                    // Bunga yang seharusnya ke tabungan, dikembalikan ke deposito baru
                    // Dr Simpanan Tabungan → Cr Simpanan Berjangka
                    $journal = $this->journalService->createSystemJournal(
                        branchId: $account->branch_id,
                        prefix: 'ARO',
                        description: "ARO Pokok+Bunga: Transfer bunga dari Tabungan {$account->savingAccount->account_no} ke Deposito {$account->account_no}",
                        entries: JournalService::drCr($tabunganCoaId, $depositoCoaId, $interestRolled),
                    );

                    app(CoaMovementService::class)->syncForJournal($journal);

                    // Debet saldo tabungan (tarik kembali bunga yang sudah dicairkan)
                    $savingAccount = $account->savingAccount;
                    $savingAccount->decrement('balance', $interestRolled);

                    \App\Models\SavingTransaction::create([
                        'transaction_no'    => $this->savingTransactionNo('ARO'),
                        'saving_account_id' => $savingAccount->id,
                        'transaction_date'  => now(),
                        'type'              => 'WITHDRAWAL',
                        'channel'           => 'INTERNAL',
                        'amount'            => $interestRolled,
                        'balance_after'     => $savingAccount->fresh()->balance,
                        'reference_no'      => 'ARO' . $account->account_no . '-' . now()->format('YmdHis'),
                        'description'       => "ARO Pokok+Bunga: Rollover ke Deposito {$account->account_no}",
                        'created_by'        => Auth::id() ?? \App\Models\User::getSystemUserId(),
                    ]);
                }
            }

            // Catat transaksi ROLLOVER pada rekening yang sama.
            DepositTransaction::create([
                'transaction_no'     => $this->depositTransactionNo('ARO'),
                'deposit_account_id' => $account->id,
                'transaction_date'   => now(),
                'type'               => 'ROLLOVER',
                'channel'            => 'INTERNAL',
                'amount'             => $newPrincipal,
                'reference_no'       => $account->account_no,
                'description'        => "Rollover {$rolloverType} → Perpanjangan deposito {$account->account_no} (Rp " . number_format($newPrincipal, 2, ',', '.') . ")",
                'created_by'         => Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);

            // Tambahkan jadwal bunga periode baru; jadwal lama tetap tersimpan.
            $simulation = $this->calculateSimulation(
                $newPrincipal,
                $account->deposit_product_id,
                $account->tenor,
                $account->interest_rate,
                $account->interest_calculation_type,
                $newPlacementDate
            );

            $monthOffset = (int) $account->schedules()->max('month_index');
            foreach ($simulation['schedule'] as $row) {
                DepositSchedule::create([
                    'deposit_account_id' => $account->id,
                    'month_index'        => $monthOffset + (int) $row['month'],
                    'schedule_date'      => $row['date'],
                    'gross_interest'     => $row['gross_interest'],
                    'tax_amount'         => $row['tax'],
                    'net_interest'       => $row['net_interest'],
                    'status'             => 'PENDING',
                ]);
            }

            return 'ROLLED_OVER';
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST TRANSACTION (backward-compat)
    // ─────────────────────────────────────────────────────────────────────────

    public function postTransaction(DepositAccount $account, string $type, float $amount, string $description = '', string $channel = 'CASH'): DepositTransaction
    {
        return DB::transaction(function () use ($account, $type, $amount, $description, $channel) {
            $channel = SettlementEngine::normalizeChannel($channel);
            $journal = $type === 'PLACEMENT'
                ? $this->postPlacementJournal($account, $channel)
                : $this->postWithdrawalJournal($account, $channel, $amount);

            return DepositTransaction::create([
                'transaction_no'     => 'DTX' . now()->format('YmdHis') . rand(100, 999),
                'deposit_account_id' => $account->id,
                'transaction_date'   => now(),
                'type'               => $type,
                'channel'            => $channel,
                'amount'             => $amount,
                'journal_id'         => $journal->id,
                'reference_no'       => $account->account_no,
                'description'        => $description,
                'created_by'         => Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BILYET MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    public function registerBilyetRange(array $data): int
    {
        $prefix    = $data['prefix'];
        $month     = $data['month'];
        $year      = $data['year'];
        $startSeq  = (int) $data['start_sequence'];
        $endSeq    = (int) $data['end_sequence'];
        $branchId  = $data['branch_id'];
        $padding   = (int)($data['padding'] ?? 5);
        $createdBy = $data['created_by'] ?? Auth::id() ?? \App\Models\User::getSystemUserId();
        $now       = now();

        $rows = [];
        for ($seq = $startSeq; $seq <= $endSeq; $seq++) {
            $shortYear    = substr($year, -2);
            $paddedSeq    = str_pad($seq, $padding, '0', STR_PAD_LEFT);
            $kodeBilyet   = $prefix . $paddedSeq . '/' . $month . '/' . $shortYear;
            $rows[] = [
                'bilyet_number' => $paddedSeq,
                'kode_bilyet'   => $kodeBilyet,
                'sequence'      => $seq,
                'branch_id'     => $branchId,
                'status'        => 'AVAILABLE',
                'created_by'    => $createdBy,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            \App\Models\DepositBilyet::insert($chunk);
        }

        return count($rows);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE JOURNAL HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function postPlacementJournal(
        DepositAccount $account,
        string $channel,
        ?int $coaOverrideId = null,
        ?SavingAccount $sourceSavingAccount = null
    ): \App\Models\Journal
    {
        $product    = $account->product;
        $liabCoaId  = $product->liability_coa_id
            ?? throw new \Exception('COA Simpanan Berjangka belum diatur.');

        if (SettlementEngine::isInternal($channel)) {
            $savingAccount = $sourceSavingAccount?->loadMissing('product');
            $sourceCoaId = $savingAccount?->product?->liability_coa_id
                ?? throw new \Exception('COA kewajiban rekening simpanan sumber dana belum diatur.');

            return $this->journalService->createSystemJournal(
                branchId: $account->branch_id,
                prefix: 'DEP',
                description: "Penempatan Deposito {$account->account_no} — Dari Simpanan {$savingAccount->account_no}",
                entries: JournalService::drCr($sourceCoaId, $liabCoaId, $account->amount),
            );
        }

        $assetCoaId = $this->settlementEngine->resolveForDeposit($product, $channel, $coaOverrideId);

        return $this->journalService->createSystemJournal(
            branchId: $account->branch_id,
            prefix: 'DEP',
            description: "Penempatan Deposito {$account->account_no} — Channel: {$channel}",
            entries: JournalService::drCr($assetCoaId, $liabCoaId, $account->amount),
        );
    }

    private function postWithdrawalJournal(DepositAccount $account, string $channel, float $amount): \App\Models\Journal
    {
        $product    = $account->product;
        $assetCoaId = $this->settlementEngine->resolveForDeposit($product, $channel);
        $liabCoaId  = $product->liability_coa_id
            ?? throw new \Exception('COA Simpanan Berjangka belum diatur.');

        return $this->journalService->createSystemJournal(
            branchId: $account->branch_id,
            prefix: 'DPW',
            description: "Penarikan Deposito {$account->account_no} — Channel: {$channel}",
            entries: JournalService::drCr($liabCoaId, $assetCoaId, $amount),
        );
    }

    private function depositTransactionNo(string $prefix): string
    {
        do {
            $number = $prefix . now()->format('YmdHis') . random_int(100, 999);
        } while (DepositTransaction::where('transaction_no', $number)->exists());

        return $number;
    }

    private function savingTransactionNo(string $prefix): string
    {
        do {
            $number = $prefix . now()->format('YmdHis') . random_int(100, 999);
        } while (\App\Models\SavingTransaction::where('transaction_no', $number)->exists());

        return $number;
    }
}
