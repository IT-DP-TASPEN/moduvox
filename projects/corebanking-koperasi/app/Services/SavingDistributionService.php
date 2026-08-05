<?php

namespace App\Services;

use App\Models\SavingAccount;
use App\Models\SavingDistribution;
use App\Models\SavingDistributionDetail;
use App\Models\SavingTransaction;
use App\Models\SavingProduct;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * SavingDistributionService
 *
 * Mendistribusikan KREDIT atau DEBIT massal ke semua rekening tabungan aktif/pending
 * berdasarkan produk tertentu. Jurnal dibuat otomatis menggunakan COA dari
 * konfigurasi produk (liability_coa_id) dan COA lawan yang dipilih operator.
 *
 * KREDIT (bonus, bagi hasil, dll):
 *   Dr: counterpart_coa_id (sumber dana)
 *   Cr: liability_coa_id (per rekening, per produk)
 *
 * DEBIT (potongan, iuran, dll):
 *   Dr: liability_coa_id (per rekening, per produk)
 *   Cr: counterpart_coa_id (tujuan dana)
 */
class SavingDistributionService
{
    public function __construct(
        private readonly JournalService $journalService,
        private readonly SettlementEngine $settlementEngine,
    ) {}

    /**
     * Eksekusi distribusi dana.
     *
     * @param  array $data  Validated distribution data
     * @return SavingDistribution
     * @throws Exception
     */
    public function executeDistribution(array $data): SavingDistribution
    {
        return DB::transaction(function () use ($data) {
            if (! empty($data['distribution_no']) && ($existing = SavingDistribution::where('distribution_no', $data['distribution_no'])->first())) {
                return $existing;
            }

            $product = SavingProduct::findOrFail($data['saving_product_id']);

            // Pastikan produk punya COA kewajiban
            if (!$product->liability_coa_id) {
                throw new Exception("Produk [{$product->name}] belum memiliki COA Kewajiban (liability_coa_id). Harap konfigurasi di Master Produk Simpanan.");
            }

            $liabilityCoaId  = $product->liability_coa_id;
            $type             = $data['distribution_type']; // CREDIT | DEBIT

            // Tentukan COA Lawan secara otomatis dari produk berdasarkan channel
            $requestedChannel = strtoupper($data['channel'] ?? 'CASH');
            $channel = $requestedChannel === 'COA' ? 'COA' : SettlementEngine::normalizeChannel($requestedChannel);
            if ($channel === 'COA' && empty($data['coa_override_id'])) {
                throw new Exception('COA transaksi wajib dipilih.');
            }
            $counterpartCoaId = $this->settlementEngine->resolveForSaving($product, $channel, $data['coa_override_id'] ?? null);

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Tidak ada data rekening untuk didistribusikan.");
            }

            if (empty($data['distribution_no']) && ($existing = $this->findExistingDistribution($data, $channel, $counterpartCoaId))) {
                return $existing;
            }

            $distributionNo = $data['distribution_no'] ?? self::generateDistributionNo();

            $duplicateAccountNos = collect($items)
                ->pluck('account_no')
                ->map(fn ($accountNo) => trim((string) $accountNo))
                ->filter()
                ->countBy()
                ->filter(fn ($count) => $count > 1);

            if ($duplicateAccountNos->isNotEmpty()) {
                $examples = $duplicateAccountNos
                    ->take(5)
                    ->map(fn ($count, $accountNo) => "{$accountNo} ({$count}x)")
                    ->implode(', ');

                throw new Exception("File CSV mengandung rekening duplikat: {$examples}. Gabungkan nominal per rekening menjadi satu baris sebelum approval.");
            }

            // Loop untuk memuat data rekening dan validasi
            $totalAmount = 0;
            $accountCount = 0;

            // Load and validate all accounts first
            $resolvedAccounts = [];
            foreach ($items as $item) {
                $accountNo = trim($item['account_no']);
                $amount = (float) $item['amount'];
                $note = trim($item['note'] ?? '');

                if ($amount <= 0) {
                    throw new Exception("Nominal untuk rekening {$accountNo} harus lebih besar dari 0.");
                }

                $account = SavingAccount::where('account_no', $accountNo)
                    ->where('saving_product_id', $product->id)
                    ->whereIn('status', ['ACTIVE', 'PENDING'])
                    ->first();

                if (!$account) {
                    throw new Exception("Rekening aktif/pending [{$accountNo}] tidak ditemukan pada produk [{$product->name}].");
                }

                // PENDING account hanya boleh untuk CREDIT (pengaktifan massal)
                if ($account->status === 'PENDING' && $type === 'DEBIT') {
                    throw new Exception("Rekening [{$accountNo}] masih berstatus PENDING dan tidak dapat didebet. Hanya transaksi KREDIT yang diizinkan untuk mengaktifkan rekening.");
                }

                // Validasi saldo untuk DEBIT
                if ($type === 'DEBIT' && $account->effective_balance < $amount) {
                    throw new Exception("Saldo rekening {$accountNo} tidak mencukupi untuk didebet sebesar Rp " . number_format($amount, 0, ',', '.') . ".");
                }

                $resolvedAccounts[] = [
                    'account' => $account,
                    'amount'  => $amount,
                    'note'    => $note,
                ];

                $totalAmount += $amount;
                $accountCount++;
            }

            // Buat header distribusi dulu
            $distribution = SavingDistribution::create([
                'distribution_no'     => $distributionNo,
                'distribution_type'   => $type,
                'channel'             => $channel,
                'saving_product_id'   => $product->id,
                'counterpart_coa_id'  => $counterpartCoaId,
                'amount_per_account'  => null, // Nullable karena nominal per rekening dapat bervariasi
                'total_amount'        => $totalAmount,
                'account_count'       => $accountCount,
                'description'         => $data['description'] ?? null,
                'effective_date'      => $data['effective_date'],
                'status'              => 'EXECUTED',
                'executed_at'         => now(),
                'executed_by'         => Auth::id() ?? \App\Models\User::getSystemUserId(),
                'created_by'          => Auth::id() ?? \App\Models\User::getSystemUserId(),
                'updated_by'          => Auth::id() ?? \App\Models\User::getSystemUserId(),
            ]);

            // Bangun journal entries detail per rekening, bukan agregat per COA.
            $typeLabel   = $type === 'CREDIT' ? 'Kredit' : 'Debit';
            $entries     = [];

            foreach ($resolvedAccounts as $resolved) {
                $account = $resolved['account'];
                $amount  = $resolved['amount'];
                $note = $resolved['note'];
                $description = $this->distributionLineDescription($typeLabel, $distributionNo, $account->account_no, $data['description'] ?? null, $note);

                if ($type === 'CREDIT') {
                    // Dr counterpart → Cr liability per rekening
                    $entries[] = [
                        'coa_id' => $counterpartCoaId,
                        'reference_no' => $account->account_no,
                        'description' => $description,
                        'debit' => $amount,
                        'credit' => 0,
                    ];
                    $entries[] = [
                        'coa_id' => $liabilityCoaId,
                        'reference_no' => $account->account_no,
                        'description' => $description,
                        'debit' => 0,
                        'credit' => $amount,
                    ];
                } else {
                    // Dr liability per rekening → Cr counterpart
                    $entries[] = [
                        'coa_id' => $liabilityCoaId,
                        'reference_no' => $account->account_no,
                        'description' => $description,
                        'debit' => $amount,
                        'credit' => 0,
                    ];
                    $entries[] = [
                        'coa_id' => $counterpartCoaId,
                        'reference_no' => $account->account_no,
                        'description' => $description,
                        'debit' => 0,
                        'credit' => $amount,
                    ];
                }
            }

            // Buat jurnal
            $prefix = $type === 'CREDIT' ? 'DIST-CR' : 'DIST-DR';
            $journal = $this->journalService->createSystemJournal(
                branchId: $resolvedAccounts[0]['account']->branch_id,
                prefix: $prefix,
                description: "Distribusi {$typeLabel} Massal — Produk: {$product->name} — {$accountCount} Rekening — " . ($data['description'] ?? ''),
                entries: $entries,
                date: $data['effective_date'],
            );

            // Update journal_id di distribusi
            $distribution->update(['journal_id' => $journal->id]);

            // Update saldo setiap rekening + catat detail
            foreach ($resolvedAccounts as $index => $resolved) {
                $account = $resolved['account'];
                $amount  = $resolved['amount'];
                $note    = $resolved['note'];

                $balanceBefore = $account->balance;
                $wasPending    = $account->status === 'PENDING';

                if ($type === 'CREDIT') {
                    $account->increment('balance', $amount);
                    // Aktifkan rekening jika sebelumnya PENDING (pengaktifan massal)
                    if ($wasPending) {
                        $account->update(['status' => 'ACTIVE']);
                    }
                } else {
                    $account->decrement('balance', $amount);
                }

                $balanceAfter = $account->fresh()->balance;

                SavingDistributionDetail::create([
                    'saving_distribution_id' => $distribution->id,
                    'saving_account_id'      => $account->id,
                    'amount'                 => $amount,
                    'balance_before'         => $balanceBefore,
                    'balance_after'          => $balanceAfter,
                    'status'                 => 'SUCCESS',
                    'note'                   => $wasPending ? '[Diaktifkan] ' . $note : $note,
                ]);

                $transactionType = $type === 'CREDIT' ? 'DEPOSIT' : 'WITHDRAWAL';
                $transactionNo = $this->generateTransactionNo($distributionNo, (int) $account->id);
                $referenceNo = $this->generateReferenceNo($distributionNo, $account->account_no, $index + 1);

                SavingTransaction::create([
                    'transaction_no' => $transactionNo,
                    'saving_account_id' => $account->id,
                    'transaction_date' => $data['effective_date'],
                    'type' => $transactionType,
                    'channel' => $channel,
                    'amount' => $amount,
                    'balance_after' => $balanceAfter,
                    'journal_id' => $journal->id,
                    'reference_no' => $referenceNo,
                    'description' => $this->distributionMutationDescription($typeLabel, $distributionNo, $data['description'] ?? null, $note),
                    'created_by' => Auth::id() ?? \App\Models\User::getSystemUserId(),
                    'approved_by' => Auth::id() ?? \App\Models\User::getSystemUserId(),
                ]);
            }

            return $distribution;
        });
    }

    public function distributionLineDescription(string $typeLabel, string $distributionNo, string $accountNo, ?string $description, ?string $note): string
    {
        return trim("Distribusi {$typeLabel} {$distributionNo} rekening {$accountNo}"
            . ($description ? " - {$description}" : '')
            . ($note ? " ({$note})" : ''));
    }

    public function distributionMutationDescription(string $typeLabel, string $distributionNo, ?string $description, ?string $note): string
    {
        return trim("Distribusi {$typeLabel} {$distributionNo}"
            . ($description ? " - {$description}" : '')
            . ($note ? " ({$note})" : ''));
    }

    private function findExistingDistribution(array $data, string $channel, int $counterpartCoaId): ?SavingDistribution
    {
        $items = collect($data['items'] ?? []);
        $accountNos = $items->pluck('account_no')->map(fn ($accountNo) => trim((string) $accountNo))->all();
        $uniqueAccountNos = array_values(array_unique($accountNos));

        $accountsByNo = SavingAccount::whereIn('account_no', $uniqueAccountNos)
            ->where('saving_product_id', $data['saving_product_id'])
            ->pluck('id', 'account_no');

        if ($accountsByNo->count() !== count($uniqueAccountNos)) {
            return null;
        }

        $expectedLines = $items->map(fn ($item) => [
            'account_id' => (int) $accountsByNo[trim((string) $item['account_no'])],
            'amount' => number_format((float) $item['amount'], 2, '.', ''),
        ]);

        $expectedCounts = $expectedLines
            ->map(fn ($line) => $line['account_id'] . '|' . $line['amount'])
            ->countBy();

        $candidates = SavingDistribution::with('details')
            ->where('distribution_type', $data['distribution_type'])
            ->where('saving_product_id', $data['saving_product_id'])
            ->where('status', 'EXECUTED')
            ->latest()
            ->get();

        foreach ($candidates as $candidate) {
            if (
                (int) $candidate->account_count !== count($accountNos)
                || (string) $candidate->channel !== $channel
                || (int) $candidate->counterpart_coa_id !== $counterpartCoaId
                || $candidate->effective_date->toDateString() !== $data['effective_date']
                || number_format((float) $candidate->total_amount, 2, '.', '') !== number_format($items->sum(fn ($item) => (float) $item['amount']), 2, '.', '')
                || strtoupper(trim((string) ($candidate->description ?? ''))) !== strtoupper(trim((string) ($data['description'] ?? '')))
            ) {
                continue;
            }

            $detailCounts = $candidate->details
                ->map(fn ($detail) => (int) $detail->saving_account_id . '|' . number_format((float) $detail->amount, 2, '.', ''))
                ->countBy();

            if ($detailCounts->all() === $expectedCounts->all()) {
                return $candidate;
            }
        }

        return null;
    }

    private function generateTransactionNo(string $distributionNo, int $accountId): string
    {
        $base = 'DST-' . str_replace('DIST-', '', $distributionNo) . '-' . str_pad((string) $accountId, 10, '0', STR_PAD_LEFT);
        $number = $base;
        $sequence = 1;

        while (SavingTransaction::where('transaction_no', $number)->exists()) {
            $number = $base . '-' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $sequence++;
        }

        return $number;
    }

    private function generateReferenceNo(string $distributionNo, string $accountNo, int $lineNumber): string
    {
        $base = "{$distributionNo}-{$accountNo}";

        if ($lineNumber === 1 && ! SavingTransaction::where('reference_no', $base)->exists()) {
            return $base;
        }

        $number = $base . '-' . str_pad((string) $lineNumber, 2, '0', STR_PAD_LEFT);
        $sequence = $lineNumber;

        while (SavingTransaction::where('reference_no', $number)->exists()) {
            $sequence++;
            $number = $base . '-' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
        }

        return $number;
    }

    /**
     * Preview — hitung rekening yang akan terdampak tanpa eksekusi.
     */
    public function preview(int $productId, array $items, string $type, string $channel = 'CASH', ?int $coaOverrideId = null): array
    {
        $product = SavingProduct::findOrFail($productId);

        // Tentukan COA Lawan secara otomatis dari produk berdasarkan channel
        $normalizedChannel = strtoupper($channel) === 'COA' ? 'COA' : SettlementEngine::normalizeChannel($channel);
        if ($normalizedChannel === 'COA' && !$coaOverrideId) {
            throw new Exception('COA transaksi wajib dipilih.');
        }
        $counterpartCoaId = $this->settlementEngine->resolveForSaving($product, $normalizedChannel, $coaOverrideId);
        $counterpartCoa = Coa::findOrFail($counterpartCoaId);

        $totalAmount = 0;
        $accountCount = 0;
        $previewAccounts = [];
        $warnings = [];
        $hasWarnings = false;

        foreach ($items as $item) {
            $accountNo = trim($item['account_no']);
            $amount = (float) $item['amount'];
            $note = trim($item['note'] ?? '');

            $account = SavingAccount::where('account_no', $accountNo)
                ->where('saving_product_id', $productId)
                ->whereIn('status', ['ACTIVE', 'PENDING'])
                ->with('cif')
                ->first();

            $insufficient = false;
            $statusText = 'OK';

            if (!$account) {
                $warnings[] = "Rekening {$accountNo} tidak ditemukan atau tidak aktif/pending pada produk ini.";
                $hasWarnings = true;
                $statusText = 'Tidak Ditemukan';
            } elseif ($account->status === 'PENDING' && $type === 'DEBIT') {
                $warnings[] = "Rekening {$accountNo} masih PENDING dan tidak dapat didebet.";
                $hasWarnings = true;
                $insufficient = true;
                $statusText = 'PENDING — Tidak Dapat Didebet';
            } elseif ($type === 'DEBIT' && $account->effective_balance < $amount) {
                $warnings[] = "Rekening {$accountNo} memiliki saldo tidak cukup (Saldo Efektif: Rp " . number_format($account->effective_balance, 0, ',', '.') . ").";
                $hasWarnings = true;
                $insufficient = true;
                $statusText = 'Saldo Tidak Cukup';
            } elseif ($account->status === 'PENDING') {
                $statusText = 'PENDING → Akan Diaktifkan';
            }

            if ($accountCount < 10) {
                $previewAccounts[] = [
                    'account_no'    => $accountNo,
                    'cif_name'      => $account?->cif?->full_name ?? $account?->cif?->name ?? '-',
                    'balance'       => $account?->balance ?? 0,
                    'amount'        => $amount,
                    'insufficient'  => $insufficient,
                    'status_text'   => $statusText,
                    'account_status'=> $account?->status ?? 'UNKNOWN',
                    'note'          => $note,
                ];
            }

            $totalAmount += $amount;
            $accountCount++;
        }

        return [
            'product_name'         => $product->name,
            'counterpart_coa_code' => $previewAccounts ? $counterpartCoa->coa_code : '',
            'counterpart_coa_name' => $counterpartCoa->name,
            'account_count'        => $accountCount,
            'total_amount'         => $totalAmount,
            'accounts'             => $previewAccounts,
            'more_count'           => max(0, $accountCount - 10),
            'warnings'             => $warnings,
            'has_warnings'         => $hasWarnings,
        ];
    }

    /**
     * Generate distribution number.
     */
    public static function generateDistributionNo(): string
    {
        return 'DIST-' . now()->format('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
