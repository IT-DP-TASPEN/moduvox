<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\LoanProduct;
use App\Models\SavingProduct;
use App\Models\DepositProduct;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * SettlementEngine
 *
 * Resolves the correct COA based on settlement channel (CASH, ABA, or INTERNAL).
 * Prevents hardcoded COA IDs scattered across services.
 *
 * Settlement Channels:
 *   CASH     → Kas (110100) atau sub-akun kas yang dipilih user
 *   ABA/BANK → Giro pada Bank Lain (110300) atau sub-akun spesifik
 *              (110310 = Giro BRI, 110320 = Giro Mandiri, 110330 = Bank Tabungan)
 *   INTERNAL → Tabungan Nasabah — tidak ada movement kas/bank fisik.
 *
 * Fitur override:
 *   Semua resolve methods menerima optional $coaOverrideId.
 *   Jika diisi, nilai tersebut menggantikan default COA produk.
 *   Ini memungkinkan user memilih sub-akun ABA spesifik saat transaksi.
 */

class SettlementEngine
{
    public const CHANNEL_CASH     = 'CASH';
    public const CHANNEL_ABA      = 'ABA';
    public const CHANNEL_INTERNAL = 'INTERNAL';

    /**
     * Resolve the settlement asset COA ID from a LoanProduct.
     *
     * INTERNAL channel digunakan untuk auto-debit dari rekening tabungan.
     * Mengembalikan suspense_coa_id (transit) karena movement sudah di sisi tabungan.
     *
     * @param  LoanProduct $product
     * @param  string      $channel  CASH | ABA | INTERNAL
     * @return int  COA ID
     * @throws \Exception if COA not configured
     */
    public function resolveForLoan(LoanProduct $product, string $channel, ?int $coaOverrideId = null): int
    {
        $channel = strtoupper($channel);

        // Jika user memilih sub-akun spesifik (misal Giro BRI), gunakan langsung
        if ($coaOverrideId) {
            return $coaOverrideId;
        }

        $coaId = match ($channel) {
            self::CHANNEL_ABA      => $product->default_bank_coa_id
                                   ?? $product->aba_transit_coa_id,
            self::CHANNEL_INTERNAL => $product->suspense_coa_id
                                   ?? $product->aba_transit_coa_id
                                   ?? $product->default_cash_coa_id,
            default                => $product->default_cash_coa_id,
        };

        if (!$coaId) {
            throw new \Exception(
                "COA untuk channel [{$channel}] belum diatur pada produk kredit [{$product->name}]."
            );
        }

        return $coaId;
    }

    /**
     * Resolve the settlement asset COA ID from a SavingProduct.
     *
     * @param  SavingProduct $product
     * @param  string        $channel  CASH | ABA | INTERNAL
     * @return int  COA ID
     * @throws \Exception
     */
    public function resolveForSaving(SavingProduct $product, string $channel, ?int $coaOverrideId = null): int
    {
        $channel = strtoupper($channel);

        if ($coaOverrideId) {
            return $coaOverrideId;
        }

        $coaId = match ($channel) {
            self::CHANNEL_ABA      => $product->default_bank_coa_id
                                   ?? $product->aba_transit_coa_id
                                   ?? $product->default_cash_coa_id,
            self::CHANNEL_INTERNAL => $product->aba_transit_coa_id
                                   ?? $product->default_cash_coa_id,
            default                => $product->default_cash_coa_id
                                   ?? $product->default_bank_coa_id,
        };

        if (!$coaId) {
            throw new \Exception(
                "COA untuk channel [{$channel}] belum diatur pada produk simpanan [{$product->name}]."
            );
        }

        return $coaId;
    }

    /**
     * Resolve the settlement asset COA ID from a DepositProduct.
     *
     * INTERNAL: pencairan pokok ke rekening tabungan nasabah.
     * Menggunakan aba_transit_coa_id sebagai debit transit, sisi kredit
     * langsung ke liability tabungan (caller menambah saldo SavingAccount).
     *
     * @param  DepositProduct $product
     * @param  string         $channel  CASH | ABA | INTERNAL
     * @return int  COA ID
     * @throws \Exception
     */
    public function resolveForDeposit(DepositProduct $product, string $channel, ?int $coaOverrideId = null): int
    {
        $channel = strtoupper($channel);

        if ($coaOverrideId) {
            return $coaOverrideId;
        }

        // Prefer kas_coa_id for backward compat, fallback to default_cash_coa_id
        $cashCoaId = $product->kas_coa_id
                  ?? $product->default_cash_coa_id
                  ?? $product->default_bank_coa_id;

        $bankCoaId = $product->default_bank_coa_id
                  ?? $product->aba_transit_coa_id
                  ?? $product->kas_coa_id
                  ?? $product->default_cash_coa_id;

        $internalCoaId = $product->aba_transit_coa_id
                      ?? $product->kas_coa_id
                      ?? $product->default_cash_coa_id;

        $coaId = match ($channel) {
            self::CHANNEL_ABA      => $bankCoaId,
            self::CHANNEL_INTERNAL => $internalCoaId,
            default                => $cashCoaId,
        };

        if (!$coaId) {
            throw new \Exception(
                "COA untuk channel [{$channel}] belum diatur pada produk simpanan berjangka [{$product->name}]."
            );
        }

        return $coaId;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS UNTUK DROPDOWN UI
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Ambil daftar sub-akun COA yang bisa dipilih user berdasarkan channel.
     *
     * Digunakan untuk mengisi dropdown saat user memilih channel transaksi.
     * Hanya mengembalikan akun is_leaf = true agar tidak ada parent dipilih.
     *
     * @param  string $channel  CASH | ABA
     * @return EloquentCollection
     */
    public static function getSelectableCoas(string $channel): EloquentCollection
    {
        $channel = strtoupper($channel);

        // Kode parent yang menjadi sumber sub-akun
        $parentCode = match ($channel) {
            self::CHANNEL_ABA  => '110300',  // Giro pada Bank Lain (ABA)
            self::CHANNEL_CASH => '110100',  // Kas
            default            => null,
        };

        if (!$parentCode) {
            return new EloquentCollection();
        }

        $parent = Coa::where('coa_code', $parentCode)->first();

        if (!$parent) {
            return new EloquentCollection();
        }

        // Kembalikan sub-akun leaf di bawah parent, plus parent itu sendiri jika is_leaf
        $children = Coa::where('parent_id', $parent->id)
            ->where('is_leaf', true)
            ->where('is_active', true)
            ->orderBy('coa_code')
            ->get();

        // Jika tidak ada anak (parent sendiri adalah leaf), kembalikan parent
        if ($children->isEmpty() && $parent->is_leaf) {
            return new EloquentCollection([$parent]);
        }

        return $children;
    }

    /**
     * Check if channel is ABA/BANK.
     */
    public static function isAba(string $channel): bool
    {
        return in_array(strtoupper($channel), [self::CHANNEL_ABA, 'BANK', 'TRANSFER'], true);
    }

    /**
     * Check if channel is INTERNAL (tidak ada movement kas/bank fisik).
     */
    public static function isInternal(string $channel): bool
    {
        return strtoupper($channel) === self::CHANNEL_INTERNAL;
    }

    /**
     * Normalize channel string to canonical form.
     */
    public static function normalizeChannel(string $channel): string
    {
        $upper = strtoupper($channel);
        if (self::isInternal($upper)) return self::CHANNEL_INTERNAL;
        if (self::isAba($upper))      return self::CHANNEL_ABA;
        return self::CHANNEL_CASH;
    }
}
