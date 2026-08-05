<?php

namespace App\Services;

use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use App\Models\SavingProduct;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * SavingOperationService — Refactored
 *
 * Mendukung channel CASH dan ABA untuk setiap transaksi:
 *
 *  Setoran Simpanan via tunai:
 *    Dr: 110100 (Kas)              Cr: 211000 (Simpanan Tabungan)
 *
 *  Setoran ABA:
 *    Dr: 110300 (Giro/ABA)         Cr: 211000 (Simpanan Tabungan)
 *
 *  Penarikan Simpanan via tunai:
 *    Dr: 211000 (Simpanan)         Cr: 110100 (Kas)
 *
 *  Penarikan ABA:
 *    Dr: 211000 (Simpanan)         Cr: 110300 (Giro/ABA)
 */
class SavingOperationService
{
    public function __construct(
        private readonly JournalService      $journalService,
        private readonly SettlementEngine    $settlementEngine,
        private readonly TransactionLimitService $limitService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // OPEN ACCOUNT
    // ─────────────────────────────────────────────────────────────────────────

    public function openAccount(array $data): SavingAccount
    {
        return DB::transaction(function () use ($data) {
            $product   = SavingProduct::findOrFail($data['saving_product_id']);
            $accountNo = $this->generateAccountNumber($data['branch_id'], $product);

            $account = SavingAccount::create([
                'account_no'        => $accountNo,
                'cif_id'            => $data['cif_id'],
                'saving_product_id' => $data['saving_product_id'],
                'branch_id'         => $data['branch_id'],
                'balance'           => 0,
                'status'            => 'PENDING',
                'opened_at'         => now(),
                'created_by'        => $data['created_by'] ?? Auth::id() ?? \App\Models\User::getSystemUserId(),
                'approved_by'       => Auth::id() ?? \App\Models\User::getSystemUserId(),
                'approved_at'       => now(),
            ]);

            if (!empty($data['initial_deposit']) && $data['initial_deposit'] > 0) {
                $this->deposit(
                    $account,
                    $data['initial_deposit'],
                    'Setoran Awal Pembukaan Rekening',
                    $data['channel'] ?? 'CASH',
                    $data['coa_override_id'] ?? null,
                    $data['created_by'] ?? null
                );
            }

            return $account;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DEPOSIT (SETORAN)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Post a deposit to saving account.
     *
     * @param  SavingAccount $account
     * @param  float         $amount
     * @param  string        $description
     * @param  string        $channel      CASH | ABA
     * @return SavingTransaction
     */
    public function deposit(
        SavingAccount $account,
        float $amount,
        string $description = '',
        string $channel = 'CASH',
        ?int $coaOverrideId = null,
        ?int $createdBy = null
    ): SavingTransaction {
        return DB::transaction(function () use ($account, $amount, $description, $channel, $coaOverrideId, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            $requestedChannel = strtoupper($channel);
            $channel = $requestedChannel === 'COA' ? 'COA' : SettlementEngine::normalizeChannel($channel);
            $limitChannel = $channel === 'COA' ? SettlementEngine::CHANNEL_CASH : $channel;
            $product = $account->product;

            // Validate transaction limits
            $validation = $this->limitService->validateSavingsDeposit($account, $amount, $limitChannel);
            if (!$validation['allowed']) {
                throw new Exception($validation['reason']);
            }

            // Resolve settlement COA
            if ($channel === 'COA' && !$coaOverrideId) {
                throw new Exception('COA transaksi wajib dipilih.');
            }
            $settlementCoaId = $this->settlementEngine->resolveForSaving($product, $channel, $coaOverrideId);
            $liabilityCoaId  = $product->liability_coa_id
                ?? throw new Exception("COA kewajiban produk tabungan [{$product->name}] belum diatur.");

            // Journal: Dr Settlement (Kas/ABA) → Cr Simpanan Tabungan
            $journal = $this->journalService->createSystemJournal(
                branchId: $account->branch_id,
                prefix: $channel === 'COA' ? 'SDC' : ($channel === SettlementEngine::CHANNEL_ABA ? 'SDA' : 'SDP'),
                description: "Setoran " . ($channel === 'COA' ? 'COA' : ($channel === SettlementEngine::CHANNEL_ABA ? 'ABA' : 'Tunai'))
                    . " — Tabungan {$account->account_no} — {$description}",
                entries: JournalService::drCr($settlementCoaId, $liabilityCoaId, $amount),
            );

            app(CoaMovementService::class)->syncForJournal($journal);

            // Update balance
            $account->increment('balance', $amount);

            // Activate account if pending
            if ($account->status === 'PENDING') {
                $account->update(['status' => 'ACTIVE']);
            }

            $txNo = $this->generateTransactionNo($channel === SettlementEngine::CHANNEL_ABA ? 'DEPOSIT_ABA' : 'DEPOSIT');

            return SavingTransaction::create([
                'transaction_no'    => $txNo,
                'saving_account_id' => $account->id,
                'transaction_date'  => now(),
                'type'              => 'DEPOSIT',
                'channel'           => $channel,
                'amount'            => $amount,
                'balance_after'     => $account->fresh()->balance,
                'journal_id'        => $journal->id,
                'reference_no'      => $journal->reference_no,
                'description'       => $description,
                'created_by'        => $createdBy,
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WITHDRAWAL (PENARIKAN)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Post a withdrawal from saving account.
     *
     * @param  SavingAccount $account
     * @param  float         $amount
     * @param  string        $description
     * @param  string        $channel      CASH | ABA
     * @return SavingTransaction
     */
    public function withdrawal(
        SavingAccount $account,
        float $amount,
        string $description = '',
        string $channel = 'CASH',
        ?int $coaOverrideId = null,
        ?int $createdBy = null
    ): SavingTransaction {
        return DB::transaction(function () use ($account, $amount, $description, $channel, $coaOverrideId, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            $requestedChannel = strtoupper($channel);
            $channel = $requestedChannel === 'COA' ? 'COA' : SettlementEngine::normalizeChannel($channel);
            $limitChannel = $channel === 'COA' ? SettlementEngine::CHANNEL_CASH : $channel;
            $product = $account->product;

            // Validate effective balance first
            if ($amount > $account->effective_balance) {
                throw new Exception(
                    "Saldo efektif tidak mencukupi. Maksimal penarikan: Rp "
                        . number_format($account->effective_balance, 2, ',', '.')
                );
            }

            // Validate transaction limits
            $validation = $this->limitService->validateSavingsWithdrawal($account, $amount, $limitChannel);
            if (!$validation['allowed']) {
                throw new Exception($validation['reason']);
            }

            // Resolve settlement COA
            if ($channel === 'COA' && !$coaOverrideId) {
                throw new Exception('COA transaksi wajib dipilih.');
            }
            $settlementCoaId = $this->settlementEngine->resolveForSaving($product, $channel, $coaOverrideId);
            $liabilityCoaId  = $product->liability_coa_id
                ?? throw new Exception("COA kewajiban produk tabungan [{$product->name}] belum diatur.");

            // Journal: Dr Simpanan Tabungan → Cr Settlement (Kas/ABA)
            $journal = $this->journalService->createSystemJournal(
                branchId: $account->branch_id,
                prefix: $channel === 'COA' ? 'SWC' : ($channel === SettlementEngine::CHANNEL_ABA ? 'SWA' : 'SDW'),
                description: "Penarikan " . ($channel === 'COA' ? 'COA' : ($channel === SettlementEngine::CHANNEL_ABA ? 'ABA' : 'Tunai'))
                    . " — Tabungan {$account->account_no} — {$description}",
                entries: JournalService::drCr($liabilityCoaId, $settlementCoaId, $amount),
            );

            app(CoaMovementService::class)->syncForJournal($journal);

            // Update balance
            $account->decrement('balance', $amount);

            $txNo = $this->generateTransactionNo($channel === SettlementEngine::CHANNEL_ABA ? 'WITHDRAWAL_ABA' : 'WITHDRAWAL');

            return SavingTransaction::create([
                'transaction_no'    => $txNo,
                'saving_account_id' => $account->id,
                'transaction_date'  => now(),
                'type'              => 'WITHDRAWAL',
                'channel'           => $channel,
                'amount'            => $amount,
                'balance_after'     => $account->fresh()->balance,
                'journal_id'        => $journal->id,
                'reference_no'      => $journal->reference_no,
                'description'       => $description,
                'created_by'        => $createdBy,
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST TRANSACTION (backward-compat wrapper)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Backward-compatible method for older callers that pass type directly.
     * Maps to deposit() or withdrawal().
     */
    public function postTransaction(
        SavingAccount $account,
        string $type,
        float $amount,
        string $description = '',
        ?string $referenceNo = null,
        string $channel = 'CASH',
        ?int $coaOverrideId = null,
        ?int $createdBy = null
    ): SavingTransaction {
        if (in_array($type, ['DEPOSIT', 'TRANSFER_IN'], true)) {
            return $this->deposit($account, $amount, $description, $channel, $coaOverrideId, $createdBy);
        }

        if (in_array($type, ['WITHDRAWAL', 'TRANSFER_OUT'], true)) {
            return $this->withdrawal($account, $amount, $description, $channel, $coaOverrideId, $createdBy);
        }

        throw new Exception("Transaction type [{$type}] tidak dikenali.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TRANSFER (INTERNAL)
    // ─────────────────────────────────────────────────────────────────────────

    public function postTransfer(
        SavingAccount $fromAccount,
        SavingAccount $toAccount,
        float $amount,
        string $description = '',
        ?string $referenceNo = null,
        ?int $createdBy = null
    ): \App\Models\Journal {
        return DB::transaction(function () use ($fromAccount, $toAccount, $amount, $description, $referenceNo, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            if ($amount > $fromAccount->effective_balance) {
                throw new Exception(
                    "Saldo efektif tidak mencukupi untuk transfer. Maksimal: Rp "
                        . number_format($fromAccount->effective_balance, 2, ',', '.')
                );
            }

            $entries = [
                ['coa_id' => $fromAccount->product->liability_coa_id, 'debit' => $amount, 'credit' => 0],
                ['coa_id' => $toAccount->product->liability_coa_id,   'debit' => 0,       'credit' => $amount],
            ];

            $journal = $this->journalService->createSystemJournal(
                branchId: $fromAccount->branch_id,
                prefix: 'TRF',
                description: "Transfer Internal: {$fromAccount->account_no} → {$toAccount->account_no} — {$description}",
                entries: $entries,
            );

            $refBase = $referenceNo ?: $journal->reference_no;
            $txNo    = $this->generateTransactionNo('TRANSFER');

            $fromAccount->decrement('balance', $amount);
            $toAccount->increment('balance', $amount);

            SavingTransaction::create([
                'transaction_no'    => $txNo . 'OUT',
                'saving_account_id' => $fromAccount->id,
                'transaction_date'  => now(),
                'type'              => 'TRANSFER_OUT',
                'channel'           => 'INTERNAL',
                'amount'            => $amount,
                'balance_after'     => $fromAccount->fresh()->balance,
                'journal_id'        => $journal->id,
                'reference_no'      => $refBase,
                'description'       => "Transfer ke {$toAccount->account_no} — {$description}",
                'created_by'        => $createdBy,
            ]);

            SavingTransaction::create([
                'transaction_no'    => $txNo . 'IN',
                'saving_account_id' => $toAccount->id,
                'transaction_date'  => now(),
                'type'              => 'TRANSFER_IN',
                'channel'           => 'INTERNAL',
                'amount'            => $amount,
                'balance_after'     => $toAccount->fresh()->balance,
                'journal_id'        => $journal->id,
                'reference_no'      => $refBase . '-IN',
                'description'       => "Transfer dari {$fromAccount->account_no} — {$description}",
                'created_by'        => $createdBy,
            ]);

            return $journal;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REVERSAL
    // ─────────────────────────────────────────────────────────────────────────

    public function reverseTransaction(SavingTransaction $originalTrx, string $reason = '', ?int $createdBy = null): SavingTransaction
    {
        return DB::transaction(function () use ($originalTrx, $reason, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            if ($originalTrx->type === 'REVERSAL') {
                throw new Exception('Transaksi reversal tidak dapat di-reverse kembali.');
            }

            if (SavingTransaction::where('type', 'REVERSAL')->where('reference_no', $originalTrx->transaction_no)->exists()) {
                throw new Exception('Transaksi ini sudah pernah di-reversal.');
            }

            $account = $originalTrx->account;
            $amount  = $originalTrx->amount;

            // Reverse journal
            $revJournal = $this->journalService->reverseJournal(
                $originalTrx->journal,
                $reason,
                autoApprove: true
            );

            // Reverse balance
            $isIncrease = in_array($originalTrx->type, ['DEPOSIT', 'TRANSFER_IN', 'INTEREST'], true);
            $newBalance  = $isIncrease ? $account->balance - $amount : $account->balance + $amount;
            $account->update(['balance' => $newBalance]);

            return SavingTransaction::create([
                'transaction_no'    => 'REV' . now()->format('YmdHis') . rand(100, 999),
                'saving_account_id' => $account->id,
                'transaction_date'  => now(),
                'type'              => 'REVERSAL',
                'channel'           => $originalTrx->channel ?? 'CASH',
                'amount'            => $amount,
                'balance_after'     => $newBalance,
                'journal_id'        => $revJournal->id,
                'reference_no'      => $originalTrx->transaction_no,
                'description'       => "Koreksi [{$originalTrx->transaction_no}]: {$reason}",
                'created_by'        => $createdBy,
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BLOCK / UNBLOCK
    // ─────────────────────────────────────────────────────────────────────────

    public function blockBalance(SavingAccount $account, float $amount, string $reason = '', ?string $referenceNo = null, ?int $createdBy = null): SavingTransaction
    {
        return DB::transaction(function () use ($account, $amount, $reason, $referenceNo, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            if ($amount > $account->effective_balance) {
                throw new Exception('Dana yang tersedia tidak mencukupi untuk diblokir.');
            }

            $referenceNo = $referenceNo ?: strtoupper(bin2hex(random_bytes(6)));

            \App\Models\SavingBlock::create([
                'saving_account_id' => $account->id,
                'amount'            => $amount,
                'reference_no'      => $referenceNo,
                'reason'            => $reason,
                'status'            => 'ACTIVE',
                'created_by'        => $createdBy,
            ]);

            $account->increment('blocked_balance', $amount);

            return SavingTransaction::create([
                'transaction_no'    => 'BLK' . now()->format('YmdHis') . rand(100, 999),
                'saving_account_id' => $account->id,
                'transaction_date'  => now(),
                'type'              => 'BLOCK',
                'channel'           => 'INTERNAL',
                'amount'            => $amount,
                'balance_after'     => $account->balance,
                'reference_no'      => 'BLK-' . $referenceNo,
                'description'       => "Blokir Saldo: {$reason} — Ref: {$referenceNo}",
                'created_by'        => $createdBy,
            ]);
        });
    }

    public function unblockBalance(SavingAccount $account, float $amount, string $reason = '', $blockId = null, ?int $createdBy = null): SavingTransaction
    {
        return DB::transaction(function () use ($account, $amount, $reason, $blockId, $createdBy) {
            $createdBy ??= Auth::id() ?? \App\Models\User::getSystemUserId();
            if ($blockId) {
                $block = \App\Models\SavingBlock::findOrFail($blockId);
                if ($block->status !== 'ACTIVE') {
                    throw new Exception('Blokir ini sudah pernah dibuka sebelumnya.');
                }
                $block->update(['status' => 'RELEASED', 'released_by' => Auth::id() ?? \App\Models\User::getSystemUserId(), 'released_at' => now()]);
                $amount = $block->amount;
            }

            if ($amount > $account->blocked_balance) {
                throw new Exception('Nominal unblock melebihi saldo terblokir saat ini.');
            }

            $account->decrement('blocked_balance', $amount);

            return SavingTransaction::create([
                'transaction_no'    => 'UBK' . now()->format('YmdHis') . rand(100, 999),
                'saving_account_id' => $account->id,
                'transaction_date'  => now(),
                'type'              => 'UNBLOCK',
                'channel'           => 'INTERNAL',
                'amount'            => $amount,
                'balance_after'     => $account->balance,
                'description'       => $reason . (isset($block) ? " — Ref: {$block->reference_no}" : ''),
                'reference_no'      => strtoupper(bin2hex(random_bytes(6))),
                'created_by'        => $createdBy,
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function generateAccountNumber($branchId, SavingProduct $product): string
    {
        $branch      = \App\Models\Branch::findOrFail($branchId);
        $branchCode  = $branch->branch_code ?? str_pad($branchId, 3, '0', STR_PAD_LEFT);
        $productCode = $product->product_code;
        $prefix      = $productCode . $branchCode;

        $latestAccount = SavingAccount::where('account_no', 'like', $prefix . '%')
            ->orderBy('account_no', 'desc')
            ->first();

        $sequence = 1;
        if ($latestAccount) {
            $lastSequence = substr($latestAccount->account_no, strlen($prefix));
            $sequence     = intval($lastSequence) + 1;
        }

        return $prefix . str_pad($sequence, 11, '0', STR_PAD_LEFT);
    }

    private function generateTransactionNo(string $type): string
    {
        $prefix = match (strtoupper($type)) {
            'DEPOSIT'       => 'SDP',
            'DEPOSIT_ABA'   => 'SDA',
            'WITHDRAWAL'    => 'SDW',
            'WITHDRAWAL_ABA' => 'SWA',
            'TRANSFER'      => 'TRF',
            'INTEREST'      => 'SIN',
            'TAX'           => 'TAX',
            'REVERSAL'      => 'REV',
            default         => 'STX',
        };

        return $prefix . now()->format('YmdHis') . rand(100, 999);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TERBILANG UTILITY
    // ─────────────────────────────────────────────────────────────────────────

    public static function terbilang(float|int $number): string
    {
        $number = (int) abs($number);
        $satuan = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas',
            'dua belas',
            'tiga belas',
            'empat belas',
            'lima belas',
            'enam belas',
            'tujuh belas',
            'delapan belas',
            'sembilan belas'
        ];

        if ($number === 0) return 'nol';
        if ($number < 20)  return $satuan[$number];
        if ($number < 100) {
            $p = (int)($number / 10);
            $s = $number % 10;
            return ($p === 1 ? 'sepuluh' : $satuan[$p] . ' puluh') . ($s ? ' ' . $satuan[$s] : '');
        }
        if ($number < 200) return 'seratus' . ($r = $number % 100 ? ' ' . self::terbilang($r) : '');
        if ($number < 1000) {
            $r = (int)($number / 100);
            $s = $number % 100;
            return $satuan[$r] . ' ratus' . ($s ? ' ' . self::terbilang($s) : '');
        }
        if ($number < 2000) return 'seribu' . ($s = $number % 1000 ? ' ' . self::terbilang($s) : '');
        if ($number < 1_000_000) {
            $r = (int)($number / 1000);
            $s = $number % 1000;
            return self::terbilang($r) . ' ribu' . ($s ? ' ' . self::terbilang($s) : '');
        }
        if ($number < 1_000_000_000) {
            $r = (int)($number / 1_000_000);
            $s = $number % 1_000_000;
            return self::terbilang($r) . ' juta' . ($s ? ' ' . self::terbilang($s) : '');
        }
        if ($number < 1_000_000_000_000) {
            $r = (int)($number / 1_000_000_000);
            $s = $number % 1_000_000_000;
            return self::terbilang($r) . ' miliar' . ($s ? ' ' . self::terbilang($s) : '');
        }
        $r = (int)($number / 1_000_000_000_000);
        $s = $number % 1_000_000_000_000;
        return self::terbilang($r) . ' triliun' . ($s ? ' ' . self::terbilang($s) : '');
    }
}
