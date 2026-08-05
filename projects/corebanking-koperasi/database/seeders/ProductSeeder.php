<?php

namespace Database\Seeders;

use App\Models\Coa;
use App\Models\SavingProduct;
use App\Models\DepositProduct;
use App\Models\LoanProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Product Seeder — Updated COA Mapping (Sirara Apps)
 *
 * Mapping COA mengacu pada OjkMasterCoaSeeder (coas_sirara_apps):
 *
 *  === ASET ===
 *  110100 = Kas
 *  110110 = Kas Teller
 *  110120 = Kas Kecil (Petty Cash)
 *  110200 = Giro pada Bank Indonesia
 *  110300 = Giro pada Bank Lain (ABA)
 *  110310 = Giro BRI
 *  110320 = Giro Mandiri
 *  110330 = Bank - Tabungan
 *  110400 = Penempatan Antar Bank
 *  110410 = Deposito Berjangka
 *  110500 = Dana Transit ABA (Rekening Perantara)
 *
 *  === LIABILITY ===
 *  211000 = Simpanan Tabungan
 *  211010 = Simpanan Tabungan Sukarela
 *  211020 = Simpanan Tabungan Umum
 *  211030 = Simpanan Tabungan Khusus (Pendidikan)
 *  211100 = Simpanan Pokok
 *  211110 = Simpanan Pokok - Anggota
 *  211120 = Simpanan Pokok - Umum
 *  211200 = Simpanan Wajib
 *  211210 = Simpanan Wajib - Anggota
 *  211220 = Simpanan Wajib - Umum
 *  212000 = Simpanan Berjangka
 *  212010 = Simpanan Deposito Berjangka
 *  213000 = Hutang Bunga Simpanan Berjangka
 *  213010 = Hutang Bunga Deposito Berjangka
 *  214000 = Hutang Bunga Tabungan
 *  214010 = Hutang Bunga Simpanan
 *  215000 = Hutang Pajak Bunga Simpanan
 *  215010 = Hutang PPH 4(2) Final
 *  219010 = Titipan Angsuran Pinjaman
 *
 *  === REVENUE ===
 *  411010 = Pendapatan Bunga Pinjaman UMKM-Umum
 *  411110 = Pendapatan Bunga Pinjaman Pegawai BPR
 *  411120 = Pendapatan Bunga Pinjaman Pegawai TAD
 *  411210 = Pendapatan Bunga Pensiunan PNS
 *  411220 = Pendapatan Bunga Pensiunan Moduvox
 *  411300 = Pendapatan Bunga Kredit Internal
 *  411400 = Pendapatan Bunga Kredit Anggota
 *  411500 = Pendapatan Bunga Pinjaman Perusahaan Lain
 *  412010 = Pendapatan Provisi Pinjaman
 *  413010 = Pendapatan Administrasi Pinjaman
 *  414010 = Pendapatan Atas Penalti / Denda Pinjaman
 *  415000 = Pendapatan Asuransi
 *  416100 = Pendapatan Flagging
 *  418000 = Pendapatan Penalti Penarikan Simpanan Berjangka
 *  419000 = Pendapatan Administrasi Tabungan
 *
 *  === EXPENSE ===
 *  511000 = Beban Bunga Jasa dan Simpanan
 *  511100 = Beban Bunga Tabungan
 *  511200 = Beban Bunga Simpanan Berjangka
 *
 *  === ASSET PRODUKTIF ===
 *  121010 = Pinjaman pada UMKM - Umum
 *  121110 = Pinjaman Pegawai BPR
 *  121120 = Pinjaman Pegawai TAD
 *  121210 = Pinjaman Pensiun PNS
 *  121220 = Pinjaman Pensiun Moduvox
 *  121300 = Kredit Internal
 *  121400 = Kredit Anggota Umum
 *  121500 = Pinjaman Perusahaan Lain
 *  122010 = Piutang Bunga Pinjaman
 *  123000 = CKPN — Cadangan Kerugian Penurunan Nilai - Kredit
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 1;


        // Helper: get COA id by code (null-safe)
        $coa = fn(string $code) => Coa::where('coa_code', $code)->value('id');

        // ─── COA LOOKUP ────────────────────────────────────────────────────────
        // --- ASET KAS & BANK ---
        $kasId        = $coa('110100');  // Kas
        $kasTellerId  = $coa('110110');  // Kas Teller
        $kasKecilId   = $coa('110120');  // Kas Kecil (Petty Cash)
        $giroBI       = $coa('110200');  // Giro pada Bank Indonesia
        $bankAbaId    = $coa('110300');  // Giro pada Bank Lain (ABA) — parent
        $giroBriId    = $coa('110310');  // Giro BRI
        $giroMandiriId = $coa('110320'); // Giro Mandiri
        $bankTabId    = $coa('110330');  // Bank - Tabungan
        $abaTransitId = $coa('110500');  // Dana Transit ABA (Rekening Perantara)

        // --- SAVING (TABUNGAN) ---
        $tabunganId       = $coa('211000');  // Simpanan Tabungan (parent)
        $tabunganSukarela = $coa('211010');  // Simpanan Tabungan Sukarela
        $tabunganUmumId   = $coa('211020');  // Simpanan Tabungan Umum
        $tabunganKhusus   = $coa('211030');  // Simpanan Tabungan Khusus (Pendidikan)
        $simpananPokokId  = $coa('211100');  // Simpanan Pokok (parent)
        $pokokAnggotaId   = $coa('211110');  // Simpanan Pokok - Anggota
        $pokokUmumId      = $coa('211120');  // Simpanan Pokok - Umum
        $simpananWajibId  = $coa('211200');  // Simpanan Wajib (parent)
        $wajibAnggotaId   = $coa('211210');  // Simpanan Wajib - Anggota
        $wajibUmumId      = $coa('211220');  // Simpanan Wajib - Umum
        $hutangBungaTabId = $coa('214000');  // Hutang Bunga Tabungan (parent)
        $hutangBungaSimId = $coa('214010');  // Hutang Bunga Simpanan
        $bebanBungaTabId  = $coa('511100');  // Beban Bunga Tabungan
        $pajakTabId       = $coa('215000');  // Hutang Pajak Bunga Simpanan (parent)
        $pajakPph42Id     = $coa('215010');  // Hutang PPH 4(2) Final
        $adminTabId       = $coa('419000');  // Pendapatan Administrasi Tabungan
        $penaltyTabId     = $coa('414010');  // Pendapatan Atas Penalti / Denda Pinjaman

        // --- SIMPANAN BERJANGKA (DEPOSIT) ---
        $depositoId       = $coa('212000');  // Simpanan Berjangka (parent)
        $depositoBjId     = $coa('212010');  // Simpanan Deposito Berjangka
        $hutangBungaDepId = $coa('213000');  // Hutang Bunga Simpanan Berjangka (parent)
        $hutangBungaDepBjId = $coa('213010'); // Hutang Bunga Deposito Berjangka
        $bebanBungaDepId  = $coa('511200');  // Beban Bunga Simpanan Berjangka
        $bebanBungaJasaId = $coa('511000');  // Beban Bunga Jasa dan Simpanan (parent)
        $pajakDepId       = $coa('215010');  // Hutang PPH 4(2) Final (pajak bunga deposito)
        $penaltyDepId     = $coa('418000');  // Pendapatan Penalti Penarikan Simpanan Berjangka
        $adminDepId       = $coa('413010');  // Pendapatan Administrasi Pinjaman

        // --- LOAN COMMON ---
        $provisiId       = $coa('412010');  // Pendapatan Provisi Pinjaman
        $adminKreditId   = $coa('413010');  // Pendapatan Administrasi Pinjaman
        $penaltyKreditId = $coa('414010');  // Pendapatan Atas Penalti / Denda Pinjaman
        $asuransiId      = $coa('415000');  // Pendapatan Asuransi
        $flaggingId      = $coa('416100');  // Pendapatan Flagging
        $stampDutyId     = $coa('219010');  // Titipan Angsuran Pinjaman (dipakai untuk titipan materai)
        $deferredInterestId = $coa('219020'); // Bunga Diskonto Diterima Dimuka
        $ckpnId          = $coa('123000');  // CKPN — Cadangan Kerugian Penurunan Nilai - Kredit
        $suspenseId      = null;            // Rekening Suspense (tidak ada di COA baru)

        // ══════════════════════════════════════════════════════════════════════
        // 1. SAVING PRODUCTS
        // ══════════════════════════════════════════════════════════════════════
        $savingProducts = [
            [
                // Simpanan Pokok — Modal anggota, tidak berbunga
                'product_code' => '100',
                'name' => 'Simpanan Pokok',
                'is_active' => true,
                'interest_calculation_type' => 'DAILY',
                'interest_rate' => 0,
                // COA Mapping
                'liability_coa_id'                => $pokokAnggotaId,   // 211110 Simpanan Pokok - Anggota
                'interest_expense_coa_id'         => $bebanBungaTabId,  // 511100 Beban Bunga Tabungan
                'admin_fee_revenue_coa_id'        => $adminTabId,       // 419000 Pendapatan Administrasi Tabungan
                'tax_liability_coa_id'            => $pajakPph42Id,     // 215010 Hutang PPH 4(2) Final
                'accrued_interest_payable_coa_id' => $hutangBungaSimId, // 214010 Hutang Bunga Simpanan
                'interest_payable_coa_id'         => $hutangBungaSimId, // 214010 Hutang Bunga Simpanan
                'default_cash_coa_id'             => $kasId,            // 110100 Kas
                'default_bank_coa_id'             => $bankAbaId,        // 110300 Giro Bank Lain (ABA)
                'aba_transit_coa_id'              => $abaTransitId,     // 110500 Dana Transit ABA
                'penalty_revenue_coa_id'          => $penaltyTabId,     // 414010 Pendapatan Penalti
                // Constraints
                'min_initial_deposit' => 100000,
                'min_balance'         => 0,
                'created_by'          => $adminId,
                'updated_by'          => $adminId,
            ],
            [
                // Simpanan Sukarela — Tabungan umum anggota, berbunga
                'product_code' => '101',
                'name' => 'Simpanan Sukarela',
                'is_active' => true,
                'interest_calculation_type' => 'DAILY',
                'interest_rate' => 2.5,
                // COA Mapping
                'liability_coa_id'                => $tabunganSukarela, // 211010 Simpanan Tabungan Sukarela
                'interest_expense_coa_id'         => $bebanBungaTabId,  // 511100 Beban Bunga Tabungan
                'admin_fee_revenue_coa_id'        => $adminTabId,       // 419000 Pendapatan Administrasi Tabungan
                'tax_liability_coa_id'            => $pajakPph42Id,     // 215010 Hutang PPH 4(2) Final
                'accrued_interest_payable_coa_id' => $hutangBungaSimId, // 214010 Hutang Bunga Simpanan
                'interest_payable_coa_id'         => $hutangBungaSimId, // 214010 Hutang Bunga Simpanan
                'default_cash_coa_id'             => $kasId,            // 110100 Kas
                'default_bank_coa_id'             => $bankAbaId,        // 110300 Giro Bank Lain (ABA)
                'aba_transit_coa_id'              => $abaTransitId,     // 110500 Dana Transit ABA
                'penalty_revenue_coa_id'          => $penaltyTabId,     // 414010 Pendapatan Penalti
                // Constraints
                'has_admin_fee'       => true,
                'admin_fee'           => 5000,
                'min_initial_deposit' => 50000,
                'min_balance'         => 25000,
                'created_by'          => $adminId,
                'updated_by'          => $adminId,
            ],
            [
                // Simpanan Wajib — Setoran rutin bulanan anggota, tidak berbunga
                'product_code' => '103',
                'name' => 'Simpanan Wajib',
                'is_active' => true,
                'interest_calculation_type' => 'DAILY',
                'interest_rate' => 0,
                // COA Mapping
                'liability_coa_id'                => $wajibAnggotaId,   // 211210 Simpanan Wajib - Anggota
                'interest_expense_coa_id'         => $bebanBungaTabId,  // 511100 Beban Bunga Tabungan
                'admin_fee_revenue_coa_id'        => $adminTabId,       // 419000 Pendapatan Administrasi Tabungan
                'tax_liability_coa_id'            => $pajakPph42Id,     // 215010 Hutang PPH 4(2) Final
                'accrued_interest_payable_coa_id' => $hutangBungaSimId, // 214010 Hutang Bunga Simpanan
                'interest_payable_coa_id'         => $hutangBungaSimId, // 214010 Hutang Bunga Simpanan
                'default_cash_coa_id'             => $kasId,            // 110100 Kas
                'default_bank_coa_id'             => $bankAbaId,        // 110300 Giro Bank Lain (ABA)
                'aba_transit_coa_id'              => $abaTransitId,     // 110500 Dana Transit ABA
                'penalty_revenue_coa_id'          => $penaltyTabId,     // 414010 Pendapatan Penalti
                // Constraints
                'min_initial_deposit' => 0,
                'fee_name'            => 'Setoran Wajib Bulanan',
                'fee_amount'          => 50000,
                'fee_type'            => 'MONTHLY',
                'created_by'          => $adminId,
                'updated_by'          => $adminId,
            ],
        ];

        foreach ($savingProducts as $p) {
            SavingProduct::updateOrCreate(['product_code' => $p['product_code']], $p);
        }

        // ══════════════════════════════════════════════════════════════════════
        // 2. DEPOSIT PRODUCTS
        // ══════════════════════════════════════════════════════════════════════
        $depositProducts = [
            [
                'product_code'              => '201',
                'name'                      => 'Simpanan Berjangka',
                'is_active'                 => true,
                'min_term'                  => 1,
                'max_term'                  => 24,
                'term_unit'                 => 'MONTH',
                'min_amount'                => 5000000,
                'min_interest_rate'         => 3.0,
                'max_interest_rate'         => 6.5,
                'interest_period'           => 'MONTHLY',
                'interest_calculation_type' => 'DAILY',
                'tax_rate'                  => 0,
                // COA Mapping
                'liability_coa_id'                => $depositoBjId,       // 212010 Simpanan Deposito Berjangka
                'interest_expense_coa_id'         => $bebanBungaDepId,    // 511200 Beban Bunga Simpanan Berjangka
                'interest_payable_coa_id'         => $hutangBungaDepBjId, // 213010 Hutang Bunga Deposito Berjangka
                'accrued_interest_payable_coa_id' => $hutangBungaDepBjId, // 213010 (accrual)
                'tax_liability_coa_id'            => $pajakDepId,         // 215010 Hutang PPH 4(2) Final
                'admin_fee_revenue_coa_id'        => $adminDepId,         // 413010 Pendapatan Administrasi Pinjaman
                'default_cash_coa_id'             => $kasId,              // 110100 Kas
                'default_bank_coa_id'             => $bankAbaId,          // 110300 Giro pada Bank Lain (ABA)
                'kas_coa_id'                      => $kasId,              // 110100 Kas (alias)
                'aba_transit_coa_id'              => $abaTransitId,       // 110500 Dana Transit ABA
                'penalty_revenue_coa_id'          => $penaltyDepId,       // 418000 Pendapatan Penalti Penarikan SB
                'created_by'                      => $adminId,
                'updated_by'                      => $adminId,
            ],
        ];

        foreach ($depositProducts as $p) {
            DepositProduct::updateOrCreate(['product_code' => $p['product_code']], $p);
        }

        // ══════════════════════════════════════════════════════════════════════
        // 3. LOAN PRODUCTS
        // ══════════════════════════════════════════════════════════════════════
        $loanProductDefaults = fn(array $overrides): array => array_merge([
            'is_active' => true,
            'is_diskonto' => false,
            'calculation_method' => 'FLAT',
            'interest_rate_min' => 0.0,
            'interest_rate_max' => 12.0,
            'provision_rate' => 1.0,
            'penalty_rate' => 2.0,
            'tenor_min' => 1,
            'tenor_max' => 120,
            'tenor_type' => 'MONTHS',
            'accrued_interest_coa_id' => $coa('122010'),
            'accrued_interest_receivable_coa_id' => $coa('122010'),
            'provision_revenue_coa_id' => $provisiId,
            'admin_fee_revenue_coa_id' => $adminKreditId,
            'insurance_revenue_coa_id' => $asuransiId,
            'flagging_revenue_coa_id' => $flaggingId,
            'stamp_duty_payable_coa_id' => $stampDutyId,
            'penalty_revenue_coa_id' => $penaltyKreditId,
            'default_cash_coa_id' => $kasId,
            'default_bank_coa_id' => $bankAbaId,
            'aba_transit_coa_id' => $abaTransitId,
            'ckpn_coa_id' => $ckpnId,
            'suspense_coa_id' => $suspenseId,
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ], $overrides);

        $loanProducts = [
            $loanProductDefaults([
                'product_code' => '301',
                'name' => 'Pinjaman Pegawai BPR',
                'calculation_method' => 'EFFECTIVE',
                'interest_rate_min' => 6.0,
                'interest_rate_max' => 12.0,
                'tenor_min' => 6,
                'tenor_max' => 60,
                'principal_coa_id' => $coa('121110'),
                'interest_revenue_coa_id' => $coa('411110'),
                'deferred_interest_coa_id' => $deferredInterestId,
            ]),
            $loanProductDefaults([
                'product_code' => '302',
                'name' => 'Pinjaman Pegawai TAD',
                'calculation_method' => 'EFFECTIVE',
                'interest_rate_min' => 6.0,
                'interest_rate_max' => 12.0,
                'tenor_min' => 6,
                'tenor_max' => 60,
                'principal_coa_id' => $coa('121120'),
                'interest_revenue_coa_id' => $coa('411120'),
            ]),
            $loanProductDefaults([
                'product_code' => '303',
                'name' => 'Pinjaman Pensiun PNS',
                'interest_rate_min' => 5.0,
                'interest_rate_max' => 10.0,
                'tenor_min' => 12,
                'tenor_max' => 120,
                'principal_coa_id' => $coa('121210'),
                'interest_revenue_coa_id' => $coa('411210'),
                'deferred_interest_coa_id' => $deferredInterestId,
            ]),
            $loanProductDefaults([
                'product_code' => '304',
                'name' => 'Pinjaman Pensiun Moduvox',
                'interest_rate_min' => 5.0,
                'interest_rate_max' => 10.0,
                'tenor_min' => 12,
                'tenor_max' => 120,
                'principal_coa_id' => $coa('121220'),
                'interest_revenue_coa_id' => $coa('411220'),
            ]),
            $loanProductDefaults([
                'product_code' => '305',
                'name' => 'Pinjaman Diskonto Pegawai BPR',
                'is_diskonto' => true,
                'interest_rate_min' => 6.0,
                'interest_rate_max' => 12.0,
                'tenor_min' => 3,
                'tenor_max' => 60,
                'principal_coa_id' => $coa('121110'),
                'interest_revenue_coa_id' => $coa('411110'),
            ]),
            $loanProductDefaults([
                'product_code' => '306',
                'name' => 'Pinjaman Diskonto Pensiun PNS',
                'is_diskonto' => true,
                'interest_rate_min' => 5.0,
                'interest_rate_max' => 10.0,
                'tenor_min' => 6,
                'tenor_max' => 120,
                'principal_coa_id' => $coa('121210'),
                'interest_revenue_coa_id' => $coa('411210'),
            ]),
            $loanProductDefaults([
                'product_code' => '307',
                'name' => 'Pinjaman Perusahaan Lain',
                'interest_rate_min' => 8.0,
                'interest_rate_max' => 18.0,
                'tenor_min' => 1,
                'tenor_max' => 60,
                'principal_coa_id' => $coa('121500'),
                'interest_revenue_coa_id' => $coa('411500'),
            ]),
        ];

        foreach ($loanProducts as $p) {
            LoanProduct::updateOrCreate(['product_code' => $p['product_code']], $p);
        }

        $this->command->info(
            '✅ Products seeded: '
            . SavingProduct::count() . ' Saving | '
            . DepositProduct::count() . ' Deposit | '
            . LoanProduct::count() . ' Loan'
        );
    }
}
