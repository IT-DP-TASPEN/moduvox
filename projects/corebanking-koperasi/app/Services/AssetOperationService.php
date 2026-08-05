<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDepreciation;
use App\Models\AssetRentalBilling;
use App\Models\Coa;
use App\Models\Journal;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetOperationService
{
    /**
     * Generate nomor referensi jurnal aset
     */
    public function generateReferenceNo(string $prefix = 'AST'): string
    {
        $date = now()->format('Ymd');
        $latest = Journal::where('reference_no', 'like', "{$prefix}-{$date}-%")
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($latest) {
            $parts = explode('-', $latest->reference_no);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    public function recognizeRentalRevenue(
        AssetRentalBilling $billing,
        int $debitCoaId,
        int $creditCoaId,
        ?int $userId = null,
        ?string $note = null
    ): Journal {
        $billing->loadMissing('rental.asset', 'rental.rekanan');

        if ($billing->status === 'PAID') {
            throw new \Exception('Tagihan sewa ini sudah lunas.');
        }

        $debitCoa = Coa::active()->leaf()->where('type', 'LIABILITY')->find($debitCoaId);
        $creditCoa = Coa::active()->leaf()->where('type', 'REVENUE')->find($creditCoaId);

        if (!$debitCoa) {
            throw new \Exception('COA debit titipan sewa belum valid.');
        }

        if (!$creditCoa) {
            throw new \Exception('COA kredit pendapatan sewa belum valid.');
        }

        return DB::transaction(function () use ($billing, $debitCoa, $creditCoa, $userId, $note) {
            $userId ??= Auth::id();
            $rental = $billing->rental;
            $amount = (float) $billing->amount;
            $description = "PEMBAYARAN SEWA ASET {$rental->contract_no} PERIODE {$billing->billing_period}";

            $journal = Journal::create([
                'branch_id'        => $rental->branch_id,
                'reference_no'     => $this->generateReferenceNo('ARS'),
                'transaction_date' => now()->toDateString(),
                'description'      => $description,
                'journal_type'     => Journal::TYPE_SYSTEM,
                'status'           => 'APPROVED',
                'created_by'       => $userId,
                'approved_by'      => Auth::id(),
                'approved_at'      => now(),
            ]);

            JournalEntry::create([
                'journal_id'   => $journal->id,
                'coa_id'       => $debitCoa->id,
                'description'  => $description,
                'debit'        => $amount,
                'credit'       => 0,
            ]);

            JournalEntry::create([
                'journal_id'   => $journal->id,
                'coa_id'       => $creditCoa->id,
                'description'  => $description,
                'debit'        => 0,
                'credit'       => $amount,
            ]);

            return $journal;
        });
    }

    public function recognizeRentalRevenueBulk(array $data, ?int $userId = null): array
    {
        return DB::transaction(function () use ($data, $userId) {
            $count = 0;
            $total = 0;

            foreach ($data['bulk_payments'] ?? [] as $item) {
                $billing = AssetRentalBilling::with('rental.asset', 'rental.rekanan')
                    ->where('billing_period', $item['billing_period'])
                    ->whereHas('rental', fn ($query) => $query->where('contract_no', $item['contract_no']))
                    ->lockForUpdate()
                    ->first();

                if (!$billing) {
                    throw new \Exception("Baris {$item['row']}: tagihan {$item['contract_no']} periode {$item['billing_period']} tidak ditemukan.");
                }

                if (abs((float) $billing->amount - (float) $item['amount']) > 0.01) {
                    throw new \Exception("Baris {$item['row']}: nominal tidak cocok dengan tagihan sistem.");
                }

                $journal = $this->recognizeRentalRevenue(
                    $billing,
                    (int) $data['payment_debit_coa_id'],
                    (int) $data['payment_credit_coa_id'],
                    $userId,
                    $item['note'] ?? null
                );

                $billing->update([
                    'status' => 'PAID',
                    'paid_at' => now(),
                    'payment_reference' => $item['payment_reference'] ?: $journal->reference_no,
                    'notes' => $item['note'] ?: $billing->notes,
                ]);

                $count++;
                $total += (float) $billing->amount;
            }

            return ['count' => $count, 'total' => $total];
        });
    }

    /**
     * Catat jurnal pembelian aset (saat pendaftaran aset baru)
     *
     * Debit  : COA Aset Tetap (coa_aset_id)
     * Credit : COA Kas/Bank   (coa_kas_id)
     */
    public function postAssetPurchaseJournal(Asset $asset, ?int $creditCoaId = null): ?Journal
    {
        $category = $asset->category()->with('parent')->first();
        
        $coaAset = $category?->getEffectiveRule('coa_aset_id');
        $coaKas  = $creditCoaId ?: $category?->getEffectiveRule('coa_kas_id');

        if (!$category || !$coaAset || !$coaKas) {
            // Tidak ada mapping COA → skip journaling, tidak error
            return null;
        }

        if ($creditCoaId && !Coa::active()->leaf()->whereKey($creditCoaId)->exists()) {
            throw new \Exception('COA kredit pembelian aset tidak valid.');
        }

        return DB::transaction(function () use ($asset, $coaAset, $coaKas) {
            $journal = Journal::create([
                'branch_id'        => $asset->branch_id,
                'reference_no'     => $this->generateReferenceNo('AST'),
                'transaction_date' => $asset->purchase_date,
                'description'      => "Pembelian Aset: {$asset->asset_code} - {$asset->name}",
                'status'           => 'APPROVED',
                'created_by'       => Auth::id(),
                'approved_by'      => Auth::id(),
                'approved_at'      => now(),
            ]);

            // Debit Aset Tetap
            JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id'     => $coaAset,
                'debit'      => $asset->purchase_price,
                'credit'     => 0,
            ]);

            // Credit Kas/Bank
            JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id'     => $coaKas,
                'debit'      => 0,
                'credit'     => $asset->purchase_price,
            ]);

            $asset->logActivity('PURCHASE_JOURNAL', "Mencatat jurnal pembelian aset: {$asset->asset_code}", $asset, ['journal_id' => $journal->id]);

            return $journal;
        });
    }

    /**
     * Catat jurnal penyusutan untuk satu aset pada satu periode.
     *
     * Debit  : COA Beban Penyusutan     (coa_beban_penyusutan_id)
     * Credit : COA Akumulasi Penyusutan (coa_akum_penyusutan_id)
     *
     * @return Journal|null  null jika COA belum lengkap (tetap proses tanpa jurnal)
     */
    public function postDepreciationJournal(
        Asset $asset,
        AssetDepreciation $depreciation,
        string $period
    ): ?Journal {
        $category = $asset->category()->with('parent')->first();
        
        $coaBeban = $category?->getEffectiveRule('coa_beban_penyusutan_id');
        $coaAkum  = $category?->getEffectiveRule('coa_akum_penyusutan_id');

        if (!$category || !$coaBeban || !$coaAkum) {
            return null;
        }

        return DB::transaction(function () use ($asset, $depreciation, $period, $coaBeban, $coaAkum) {
            $journal = Journal::create([
                'branch_id'        => $asset->branch_id,
                'reference_no'     => $this->generateReferenceNo('DEP'),
                'transaction_date' => now()->endOfMonth(),
                'description'      => "Penyusutan Aset: {$asset->asset_code} - {$asset->name} Periode {$period}",
                'status'           => 'APPROVED',
                'created_by'       => Auth::id(),
                'approved_by'      => Auth::id(),
                'approved_at'      => now(),
            ]);

            // Debit Beban Penyusutan
            JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id'     => $coaBeban,
                'debit'      => $depreciation->depreciation_amount,
                'credit'     => 0,
            ]);

            // Credit Akumulasi Penyusutan
            JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id'     => $coaAkum,
                'debit'      => 0,
                'credit'     => $depreciation->depreciation_amount,
            ]);

            $asset->logActivity('DEPRECIATION_JOURNAL', "Mencatat jurnal penyusutan periode {$period}: {$asset->asset_code}", $asset, ['journal_id' => $journal->id, 'amount' => $depreciation->depreciation_amount]);

            return $journal;
        });
    }

    /**
     * Eksekusi penyusutan batch untuk banyak aset sekaligus (dipakai dari Depreciation Livewire).
     * Setiap aset diproses dalam satu transaksi, jurnal diposting jika COA tersedia.
     *
     * @param  array  $previewList  Array dari preview item
     * @param  string $period       Format YYYY-MM
     * @return int    Jumlah aset yang berhasil diproses
     */
    public function executeBatchDepreciation(array $previewList, string $period): int
    {
        $count = 0;

        foreach ($previewList as $item) {
            DB::transaction(function () use ($item, $period, &$count) {
                $asset = Asset::find($item['asset_id']);
                if (!$asset) return;

                // Guard duplikasi
                $exists = AssetDepreciation::where('asset_id', $asset->id)
                    ->where('period_year_month', $period)
                    ->exists();
                if ($exists) return;

                // Buat record penyusutan
                $depreciation = AssetDepreciation::create([
                    'asset_id'            => $asset->id,
                    'period_year_month'   => $period,
                    'depreciation_date'   => Carbon::createFromFormat('Y-m', $period)->endOfMonth()->toDateString(),
                    'depreciation_amount' => $item['depreciation_amount'],
                    'accumulated_depreciation_after' => round((float) $asset->accumulated_depreciation + (float) $item['depreciation_amount'], 2),
                    'book_value_after'    => $item['closing_book_value'],
                    'created_by'          => Auth::id(),
                ]);

                // Update nilai buku aset
                $asset->update([
                    'accumulated_depreciation' => $depreciation->accumulated_depreciation_after,
                    'current_book_value' => $item['closing_book_value'],
                    'updated_by'         => Auth::id(),
                ]);

                // Posting jurnal (opsional jika COA terpetakan)
                $journal = $this->postDepreciationJournal($asset, $depreciation, $period);
                if ($journal) {
                    $depreciation->update(['journal_id' => $journal->id]);
                }

                $count++;
            });
        }

        return $count;
    }
}
