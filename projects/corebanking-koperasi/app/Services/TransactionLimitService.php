<?php

namespace App\Services;

use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use App\Models\DepositAccount;
use App\Models\LoanAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * TransactionLimitService
 * 
 * Validates transaction amounts against product limits
 * - Daily transaction limit per account
 * - Monthly transaction limit per account
 * - Per-transaction maximum amount
 * - Transaction frequency limits
 */
class TransactionLimitService
{
    /**
     * Check if transaction amount is within limits for savings withdrawal
     * 
     * @param SavingAccount $account
     * @param float $amount
     * @param string $channel CASH|ABA|INTERNAL
     * @return array ['allowed' => bool, 'reason' => string|null, 'max_allowed' => float]
     */
    public function validateSavingsWithdrawal(SavingAccount $account, float $amount, string $channel = 'CASH'): array
    {
        $product = $account->product;
        $today = now()->format('Y-m-d');

        // 1. Check per-transaction limit
        $maxPerTransaction = $this->getWithdrawalLimit($product, $channel);
        if ($amount > $maxPerTransaction) {
            return [
                'allowed' => false,
                'reason' => "Jumlah penarikan melebihi limit per transaksi. Maximum: Rp " .
                    number_format($maxPerTransaction, 0, ',', '.'),
                'max_allowed' => $maxPerTransaction
            ];
        }

        // 2. Check daily limit
        $dailyWithdrawn = $this->getTodayWithdrawalTotal($account, $channel);
        $dailyLimit = $this->getDailyWithdrawalLimit($product, $channel);
        $remainingDaily = $dailyLimit - $dailyWithdrawn;

        if ($amount > $remainingDaily) {
            return [
                'allowed' => false,
                'reason' => "Penarikan hari ini sudah mencapai atau melampaui limit harian. " .
                    "Sisa limit: Rp " . number_format(max(0, $remainingDaily), 0, ',', '.'),
                'max_allowed' => max(0, $remainingDaily)
            ];
        }

        // 3. Check monthly limit (if configured)
        $monthlyLimit = $this->getMonthlyWithdrawalLimit($product, $channel);
        if ($monthlyLimit > 0) {
            $monthlyWithdrawn = $this->getMonthWithdrawalTotal($account, $channel);
            $remainingMonthly = $monthlyLimit - $monthlyWithdrawn;

            if ($amount > $remainingMonthly) {
                return [
                    'allowed' => false,
                    'reason' => "Penarikan bulan ini sudah mencapai limit bulanan. " .
                        "Sisa limit: Rp " . number_format(max(0, $remainingMonthly), 0, ',', '.'),
                    'max_allowed' => max(0, $remainingMonthly)
                ];
            }
        }

        // 4. Check frequency limit (e.g., max withdrawals per day)
        $maxFrequencyPerDay = $product->max_withdrawal_frequency_per_day ?? null;
        if ($maxFrequencyPerDay) {
            $todayCount = SavingTransaction::where('saving_account_id', $account->id)
                ->where('type', 'WITHDRAWAL')
                ->whereDate('transaction_date', $today)
                ->count();

            if ($todayCount >= $maxFrequencyPerDay) {
                return [
                    'allowed' => false,
                    'reason' => "Sudah mencapai limit penarikan harian ({$maxFrequencyPerDay}x). Silakan coba lagi besok.",
                    'max_allowed' => 0
                ];
            }
        }

        return ['allowed' => true, 'reason' => null, 'max_allowed' => $remainingDaily];
    }

    /**
     * Check if deposit amount is within limits
     */
    public function validateSavingsDeposit(SavingAccount $account, float $amount, string $channel = 'CASH'): array
    {
        $product = $account->product;
        $today = now()->format('Y-m-d');

        // 1. Check per-transaction limit
        $maxPerTransaction = $this->getDepositLimit($product, $channel);
        if ($amount > $maxPerTransaction) {
            return [
                'allowed' => false,
                'reason' => "Jumlah setoran melebihi limit per transaksi. Maximum: Rp " .
                    number_format($maxPerTransaction, 0, ',', '.'),
                'max_allowed' => $maxPerTransaction
            ];
        }

        // 2. Check daily limit
        $dailyDeposited = $this->getTodayDepositTotal($account, $channel);
        $dailyLimit = $this->getDailyDepositLimit($product, $channel);
        $remainingDaily = $dailyLimit - $dailyDeposited;

        if ($amount > $remainingDaily) {
            return [
                'allowed' => false,
                'reason' => "Setoran hari ini sudah mencapai limit harian. " .
                    "Sisa limit: Rp " . number_format(max(0, $remainingDaily), 0, ',', '.'),
                'max_allowed' => max(0, $remainingDaily)
            ];
        }

        return ['allowed' => true, 'reason' => null, 'max_allowed' => $remainingDaily];
    }

    /**
     * Check if transfer amount is within limits
     */
    public function validateTransfer(SavingAccount $fromAccount, SavingAccount $toAccount, float $amount): array
    {
        $product = $fromAccount->product;

        // Check product transfer limit
        $maxTransfer = $product->max_transfer_amount ?? 999999999;
        if ($amount > $maxTransfer) {
            return [
                'allowed' => false,
                'reason' => "Jumlah transfer melebihi limit. Maximum: Rp " .
                    number_format($maxTransfer, 0, ',', '.'),
                'max_allowed' => $maxTransfer
            ];
        }

        // Check if transfer between same CIF is allowed
        if ($fromAccount->cif_id !== $toAccount->cif_id) {
            if (!($product->allow_cross_cif_transfer ?? false)) {
                return [
                    'allowed' => false,
                    'reason' => "Transfer antar nasabah tidak diizinkan untuk produk ini.",
                    'max_allowed' => 0
                ];
            }
        }

        return ['allowed' => true, 'reason' => null, 'max_allowed' => $maxTransfer];
    }

    /**
     * Check if loan disbursement amount is reasonable
     */
    public function validateLoanDisbursement(LoanAccount $loan): array
    {
        // Note: Loan amounts are typically already validated during approval
        // This is a safety check layer

        if ($loan->principal_amount <= 0) {
            return [
                'allowed' => false,
                'reason' => "Jumlah principal tidak valid.",
                'max_allowed' => 0
            ];
        }

        $product = $loan->product;
        $maxLoan = $product->max_principal_amount ?? null;

        if ($maxLoan && $loan->principal_amount > $maxLoan) {
            return [
                'allowed' => false,
                'reason' => "Jumlah pinjaman melebihi maksimum produk. Maximum: Rp " .
                    number_format($maxLoan, 0, ',', '.'),
                'max_allowed' => $maxLoan
            ];
        }

        return ['allowed' => true, 'reason' => null, 'max_allowed' => $maxLoan ?? $loan->principal_amount];
    }

    /**
     * Check if deposit placement is within limits
     */
    public function validateDepositPlacement(DepositAccount $deposit): array
    {
        $product = $deposit->product;

        // Check minimum amount
        $minAmount = $product->min_deposit_amount ?? 0;
        if ($deposit->amount < $minAmount) {
            return [
                'allowed' => false,
                'reason' => "Jumlah deposito kurang dari minimum. Minimum: Rp " .
                    number_format($minAmount, 0, ',', '.'),
                'max_allowed' => $minAmount
            ];
        }

        // Check maximum amount
        $maxAmount = $product->max_deposit_amount ?? null;
        if ($maxAmount && $deposit->amount > $maxAmount) {
            return [
                'allowed' => false,
                'reason' => "Jumlah deposito melebihi maksimum. Maximum: Rp " .
                    number_format($maxAmount, 0, ',', '.'),
                'max_allowed' => $maxAmount
            ];
        }

        return ['allowed' => true, 'reason' => null, 'max_allowed' => $maxAmount ?? $deposit->amount];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPER METHODS
    // ─────────────────────────────────────────────────────────────────────────

    private function getWithdrawalLimit($product, string $channel): float
    {
        $key = strtolower($channel) . '_withdrawal_limit';
        return (float)($product->{$key} ?? $product->max_withdrawal_amount ?? 100000000);
    }

    private function getDepositLimit($product, string $channel): float
    {
        $key = strtolower($channel) . '_deposit_limit';
        return (float)($product->{$key} ?? $product->max_deposit_per_transaction ?? 1000000000);
    }

    private function getDailyWithdrawalLimit($product, string $channel): float
    {
        return (float)($product->daily_withdrawal_limit ?? 500000000);
    }

    private function getDailyDepositLimit($product, string $channel): float
    {
        return (float)($product->daily_deposit_limit ?? 1000000000);
    }

    private function getMonthlyWithdrawalLimit($product, string $channel): float
    {
        return (float)($product->monthly_withdrawal_limit ?? 0); // 0 = no limit
    }

    private function getTodayWithdrawalTotal(SavingAccount $account, string $channel): float
    {
        $today = now()->format('Y-m-d');
        return (float) SavingTransaction::where('saving_account_id', $account->id)
            ->where('type', 'WITHDRAWAL')
            ->where('channel', $channel)
            ->whereDate('transaction_date', $today)
            ->sum('amount');
    }

    private function getTodayDepositTotal(SavingAccount $account, string $channel): float
    {
        $today = now()->format('Y-m-d');
        return (float) SavingTransaction::where('saving_account_id', $account->id)
            ->where('type', 'DEPOSIT')
            ->where('channel', $channel)
            ->whereDate('transaction_date', $today)
            ->sum('amount');
    }

    private function getMonthWithdrawalTotal(SavingAccount $account, string $channel): float
    {
        return (float) SavingTransaction::where('saving_account_id', $account->id)
            ->where('type', 'WITHDRAWAL')
            ->where('channel', $channel)
            ->whereBetween('transaction_date', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])
            ->sum('amount');
    }
}
