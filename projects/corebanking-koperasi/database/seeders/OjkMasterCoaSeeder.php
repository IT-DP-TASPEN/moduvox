<?php

namespace Database\Seeders;

use App\Models\Coa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sirara Apps — Chart of Accounts Seeder
 * Sumber: data manual coas_sirara_apps
 *
 * Format array: [coa_code, name, type, parent_code, is_leaf, is_cash]
 * parent_code null => Level 1 (root)
 */
class OjkMasterCoaSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        Coa::truncate();
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $coas = [
            // ══════════════════════════════════════════════════════════════════
            // 1 — ASET
            // ══════════════════════════════════════════════════════════════════
            ['1',      'ASET',                                               'ASSET',     null,     false, false],

            ['110000', 'ASET LANCAR',                                        'ASSET',     '1',      false, false],
            ['110100', 'KAS',                                                'ASSET',     '110000', false, true],
            ['110110', 'KAS TELLER',                                         'ASSET',     '110100', true,  true],
            ['110120', 'KAS KECIL (PETTY CASH)',                             'ASSET',     '110100', true,  true],
            ['110200', 'GIRO PADA BANK INDONESIA',                           'ASSET',     '110000', true,  true],
            ['110300', 'GIRO PADA BANK LAIN (ABA)',                          'ASSET',     '110000', false, true],
            ['110310', 'GIRO BRI',                                           'ASSET',     '110300', true,  true],
            ['110320', 'GIRO MANDIRI',                                       'ASSET',     '110300', true,  true],
            ['110330', 'BANK - TABUNGAN',                                    'ASSET',     '110300', true,  true],
            ['110400', 'PENEMPATAN ANTAR BANK',                              'ASSET',     '110000', false, false],
            ['110410', 'DEPOSITO BERJANGKA',                                 'ASSET',     '110400', true,  false],
            ['110500', 'DANA TRANSIT ABA (REKENING PERANTARA)',              'ASSET',     '110000', true,  false],
            ['110600', 'PENDAPATAN BUNGA YANG MASIH AKAN DITERIMA',          'ASSET',     '110000', false, false],
            ['110610', 'PIUTANG BUNGA PINJAMAN',                             'ASSET',     '110600', true,  false],
            ['110700', 'UANG MUKA DAN BIAYA DIBAYAR DIMUKA',                 'ASSET',     '110000', false, false],
            ['110710', 'UANG MUKA KERJA UMUM',                               'ASSET',     '110700', true,  false],
            ['110720', 'BEBAN DIBAYAR DIMUKA',                               'ASSET',     '110700', true,  false],
            ['110800', 'ASET PAJAK DIBAYAR DIMUKA',                          'ASSET',     '110000', false, false],
            ['110810', 'PAJAK DIBAYAR DIMUKA (PPH 25)',                      'ASSET',     '110800', true,  false],
            ['110820', 'PAJAK DIBAYAR DIMUKA (PPH 23)',                      'ASSET',     '110800', true,  false],
            ['110900', 'PIUTANG KLAIM ASURANSI',                             'ASSET',     '110000', true,  false],

            ['120000', 'ASET PRODUKTIF (KREDIT)',                            'ASSET',     '1',      false, false],
            ['121000', 'KREDIT YANG DIBERIKAN - UMUM',                       'ASSET',     '120000', false, false],
            ['121010', 'PINJAMAN PADA UMKM - UMUM',                          'ASSET',     '121000', true,  false],
            ['121100', 'KREDIT PEGAWAI AKTIF',                               'ASSET',     '121000', false, false],
            ['121110', 'PINJAMAN PEGAWAI BPR',                               'ASSET',     '121100', true,  false],
            ['121120', 'PINJAMAN PEGAWAI TAD',                               'ASSET',     '121100', true,  false],
            ['121200', 'KREDIT PENSIUNAN',                                   'ASSET',     '121000', false, false],
            ['121210', 'PINJAMAN PENSIUN PNS',                               'ASSET',     '121200', true,  false],
            ['121220', 'PINJAMAN PENSIUN MODUVOX',                         'ASSET',     '121200', true,  false],
            ['121300', 'KREDIT INTERNAL',                                    'ASSET',     '121000', true,  false],
            ['121400', 'KREDIT ANGGOTA UMUM',                                'ASSET',     '121000', true,  false],
            ['121500', 'PINJAMAN PERUSAHAAN LAIN',                           'ASSET',     '121000', true,  false],
            ['122000', 'PIUTANG BUNGA KREDIT YANG MASIH BERJALAN',           'ASSET',     '120000', false, false],
            ['122010', 'PIUTANG BUNGA PINJAMAN',                             'ASSET',     '122000', true,  false],
            ['123000', 'CKPN — CADANGAN KERUGIAN PENURUNAN NILAI - KREDIT',  'ASSET',     '120000', true,  false],
            ['124000', 'CKPN — CADANGAN KERUGIAN PENURUNAN NILAI - PIUTANG', 'ASSET',     '120000', true,  false],
            ['125000', 'ASET YANG DIAMBIL ALIH (AYDA)',                      'ASSET',     '120000', true,  false],

            ['130000', 'ASET TETAP',                                         'ASSET',     '1',      false, false],
            ['131000', 'TANAH',                                              'ASSET',     '130000', true,  false],
            ['132000', 'BANGUNAN',                                           'ASSET',     '130000', true,  false],
            ['133000', 'KENDARAAN',                                          'ASSET',     '130000', true,  false],
            ['134000', 'INVENTARIS KANTOR',                                  'ASSET',     '130000', false, false],
            ['134010', 'INVENTARIS DAN PERALATAN KANTOR',                    'ASSET',     '134000', true,  false],
            ['135000', 'ASET TAK BERWUJUD (SOFTWARE/LISENSI)',                'ASSET',     '130000', false, false],
            ['135010', 'SOFTWARE / APLIKASI CORE BANKING',                   'ASSET',     '135000', true,  false],
            ['135020', 'LISENSI SOFTWARE',                                   'ASSET',     '135000', true,  false],
            ['135030', 'HAK PATEN / HAK CIPTA',                              'ASSET',     '135000', true,  false],
            ['135040', 'PENGEMBANGAN WEBSITE & APLIKASI',                    'ASSET',     '135000', true,  false],
            ['135050', 'ASET TAK BERWUJUD LAINNYA',                          'ASSET',     '135000', true,  false],
            ['139100', 'AKUMULASI PENYUSUTAN BANGUNAN',                      'ASSET',     '130000', true,  false],
            ['139200', 'AKUMULASI PENYUSUTAN KENDARAAN',                     'ASSET',     '130000', true,  false],
            ['139300', 'AKUMULASI PENYUSUTAN PERALATAN',                     'ASSET',     '130000', true,  false],
            ['139400', 'AKUMULASI PENYUSUTAN ASET TIDAK BERWUJUD',            'ASSET',     '130000', true,  false],

            ['140000', 'ASET LAIN-LAIN',                                     'ASSET',     '1',      false, false],
            ['141000', 'AGUNAN YANG DIAMBIL ALIH',                           'ASSET',     '140000', true,  false],
            ['149000', 'REKENING ANTAR KANTOR (RAK) - ASET',                 'ASSET',     '140000', false, false],
            ['149100', 'RAK - KANTOR PUSAT',                                 'ASSET',     '149000', true,  false],
            ['149200', 'RAK - CABANG 1',                                     'ASSET',     '149000', true,  false],
            ['149999', 'REKENING ANTARA MIGRASI / SALDO AWAL',               'ASSET',     '149000', true,  false],

            // ══════════════════════════════════════════════════════════════════
            // 2 — KEWAJIBAN
            // ══════════════════════════════════════════════════════════════════
            ['2',      'KEWAJIBAN',                                          'LIABILITY', null,     false, false],

            ['210000', 'KEWAJIBAN JANGKA PENDEK',                            'LIABILITY', '2',      false, false],
            ['211000', 'SIMPANAN TABUNGAN',                                  'LIABILITY', '210000', false, false],
            ['211010', 'SIMPANAN TABUNGAN SUKARELA',                         'LIABILITY', '211000', true,  false],
            ['211020', 'SIMPANAN TABUNGAN UMUM',                             'LIABILITY', '211000', true,  false],
            ['211030', 'SIMPANAN TABUNGAN KHUSUS (PENDIDIKAN)',               'LIABILITY', '211000', true,  false],
            ['211100', 'SIMPANAN POKOK',                                     'LIABILITY', '210000', false, false],
            ['211110', 'SIMPANAN POKOK - ANGGOTA',                           'LIABILITY', '211100', true,  false],
            ['211120', 'SIMPANAN POKOK - UMUM',                              'LIABILITY', '211100', true,  false],
            ['211200', 'SIMPANAN WAJIB',                                     'LIABILITY', '210000', false, false],
            ['211210', 'SIMPANAN WAJIB - ANGGOTA',                           'LIABILITY', '211200', true,  false],
            ['211220', 'SIMPANAN WAJIB - UMUM',                              'LIABILITY', '211200', true,  false],
            ['212000', 'SIMPANAN BERJANGKA',                                 'LIABILITY', '210000', false, false],
            ['212010', 'SIMPANAN DEPOSITO BERJANGKA',                        'LIABILITY', '212000', true,  false],
            ['213000', 'HUTANG BUNGA SIMPANAN BERJANGKA',                    'LIABILITY', '210000', false, false],
            ['213010', 'HUTANG BUNGA DEPOSITO BERJANGKA',                    'LIABILITY', '213000', true,  false],
            ['214000', 'HUTANG BUNGA TABUNGAN',                              'LIABILITY', '210000', false, false],
            ['214010', 'HUTANG BUNGA SIMPANAN',                              'LIABILITY', '214000', true,  false],
            ['215000', 'HUTANG PAJAK BUNGA SIMPANAN',                        'LIABILITY', '210000', false, false],
            ['215010', 'HUTANG PPH 4(2) Final',                              'LIABILITY', '215000', true,  false],
            ['218000', 'HUTANG LAIN-LAIN JANGKA PENDEK',                     'LIABILITY', '210000', false, false],
            ['218010', 'HUTANG PPH 21',                                      'LIABILITY', '218000', true,  false],
            ['218020', 'HUTANG PPH 23',                                      'LIABILITY', '218000', true,  false],
            ['218030', 'HUTANG PPN',                                         'LIABILITY', '218000', true,  false],
            ['219000', 'KEWAJIBAN JANGKA PENDEK LAINNYA',                    'LIABILITY', '210000', false, false],
            ['219010', 'TITIPAN ANGSURAN PINJAMAN',                          'LIABILITY', '219000', true,  false],
            ['219020', 'BUNGA DISKONTO DITERIMA DIMUKA (LIABILITIES)',        'LIABILITY', '219000', true,  false],
            ['219030', 'TITIPAN PREMI ASURANSI',                             'LIABILITY', '219000', true,  false],
            ['219040', 'HUTANG KLAIM ASURANSI',                              'LIABILITY', '219000', true,  false],
            ['219050', 'PENDAPATAN DITERIMA DIMUKA',                         'LIABILITY', '219000', true,  false],
            ['219060', 'TAKSIRAN PPH BADAN',                                 'LIABILITY', '219000', true,  false],
            ['219099', 'HUTANG LAIN LAIN',                                   'LIABILITY', '219000', true,  false],

            ['220000', 'KEWAJIBAN JANGKA PANJANG',                           'LIABILITY', '2',      false, false],
            ['221000', 'PINJAMAN DITERIMA DARI BANK/LEMBAGA',                 'LIABILITY', '220000', true,  false],
            ['222000', 'DANA PROGRAM / DANA BERGULIR',                       'LIABILITY', '220000', true,  false],
            ['223000', 'PINJAMAN DARI KOPERASI LAINNYA',                     'LIABILITY', '220000', true,  false],
            ['224000', 'LIABILITAS IMBALAN KERJA',                           'LIABILITY', '220000', true,  false],

            ['290000', 'REKENING ANTAR KANTOR (RAK) - LIABILITAS',           'LIABILITY', '2',      false, false],
            ['290100', 'RAK - KANTOR PUSAT',                                 'LIABILITY', '290000', true,  false],
            ['290200', 'RAK - CABANG 1',                                     'LIABILITY', '290000', true,  false],

            // ══════════════════════════════════════════════════════════════════
            // 3 — EKUITAS
            // ══════════════════════════════════════════════════════════════════
            ['3',      'EKUITAS',                                            'EQUITY',    null,     false, false],

            ['310000', 'MODAL DAN CADANGAN',                                 'EQUITY',    '3',      false, false],
            ['311000', 'SIMPANAN POKOK (MODAL)',                              'EQUITY',    '310000', false, false],
            ['311100', 'SIMPANAN WAJIB (MODAL)',                              'EQUITY',    '311000', true,  false],
            ['311200', 'SIMPANAN KHUSUS PENYERTAAN MODAL',                   'EQUITY',    '311000', true,  false],
            ['312000', 'CADANGAN UMUM',                                      'EQUITY',    '310000', false, false],
            ['312100', 'CADANGAN KHUSUS / CADANGAN RISIKO',                  'EQUITY',    '312000', true,  false],
            ['313000', 'HIBAH / DONASI',                                     'EQUITY',    '310000', true,  false],
            ['314000', 'SHU / LABA TAHUN BERJALAN',                          'EQUITY',    '310000', true,  false],
            ['315000', 'SHU LABA TAHUN LALU',                                'EQUITY',    '310000', true,  false],

            // ══════════════════════════════════════════════════════════════════
            // 4 — PENDAPATAN
            // ══════════════════════════════════════════════════════════════════
            ['4',      'PENDAPATAN',                                         'REVENUE',   null,     false, false],

            ['410000', 'PENDAPATAN OPERASIONAL',                             'REVENUE',   '4',      false, false],
            ['411000', 'PENDAPATAN BUNGA KREDIT UMUM',                       'REVENUE',   '410000', false, false],
            ['411010', 'PENDAPATAN BUNGA PINJAMAN KREDIT UMKM-UMUM',         'REVENUE',   '411000', true,  false],
            ['411100', 'PENDAPATAN BUNGA KREDIT PEGAWAI AKTIF',              'REVENUE',   '411000', false, false],
            ['411110', 'PENDAPATAN BUNGA PINJAMAN PEGAWAI BPR',              'REVENUE',   '411100', true,  false],
            ['411120', 'PENDAPATAN BUNGA PINJAMAN PEGAWAI TAD',              'REVENUE',   '411100', true,  false],
            ['411200', 'PENDAPATAN BUNGA KREDIT PENSIUNAN',                  'REVENUE',   '411000', false, false],
            ['411210', 'PENDAPATAN BUNGA PENSIUNAN PNS',                     'REVENUE',   '411200', true,  false],
            ['411220', 'PENDAPATAN BUNGA PENSIUNAN MODUVOX',               'REVENUE',   '411200', true,  false],
            ['411300', 'PENDAPATAN BUNGA KREDIT INTERNAL',                   'REVENUE',   '411000', true,  false],
            ['411400', 'PENDAPATAN BUNGA KREDIT ANGGOTA',                    'REVENUE',   '411000', true,  false],
            ['411500', 'PENDAPATAN BUNGA PINJAMAN PERUSAHAAN LAIN',          'REVENUE',   '411000', true,  false],
            ['412000', 'PENDAPATAN PROVISI KREDIT',                          'REVENUE',   '410000', false, false],
            ['412010', 'PENDAPATAN PROVISI PINJAMAN',                        'REVENUE',   '412000', true,  false],
            ['413000', 'PENDAPATAN ADMINISTRASI',                            'REVENUE',   '410000', false, false],
            ['413010', 'PENDAPATAN ADMINISTRASI PINJAMAN',                   'REVENUE',   '413000', true,  false],
            ['414000', 'PENDAPATAN PENALTI / DENDA KREDIT',                  'REVENUE',   '410000', false, false],
            ['414010', 'PENDAPATAN ATAS PENALTI / DENDA PINJAMAN',           'REVENUE',   '414000', true,  false],
            ['415000', 'PENDAPATAN ASURANSI',                                'REVENUE',   '410000', true,  false],
            ['416000', 'PENDAPATAN NOTARIS',                                 'REVENUE',   '410000', false, false],
            ['416100', 'PENDAPATAN FLAGGING',                                'REVENUE',   '416000', true,  false],
            ['417000', 'PENDAPATAN SEWA ASET',                               'REVENUE',   '410000', true,  false],
            ['418000', 'PENDAPATAN PENALTI PENARIKAN SIMPANAN BERJANGKA',    'REVENUE',   '410000', true,  false],
            ['419000', 'PENDAPATAN ADMINISTRASI TABUNGAN',                   'REVENUE',   '410000', true,  false],

            ['420000', 'PENDAPATAN NON OPERASIONAL',                         'REVENUE',   '4',      false, false],
            ['421000', 'PENDAPATAN JASA GIRO / BUNGA BANK',                  'REVENUE',   '420000', true,  false],
            ['422000', 'PENDAPATAN INVESTASI / DIVIDEN',                     'REVENUE',   '420000', true,  false],
            ['423000', 'KEUNTUNGAN PENJUALAN ASET TETAP',                    'REVENUE',   '420000', true,  false],
            ['424000', 'PENDAPATAN LAIN-LAIN NON OPERASIONAL',               'REVENUE',   '420000', true,  false],

            // ══════════════════════════════════════════════════════════════════
            // 5 — BEBAN
            // ══════════════════════════════════════════════════════════════════
            ['5',      'BEBAN',                                              'EXPENSE',   null,     false, false],

            ['510000', 'BEBAN OPERASIONAL',                                  'EXPENSE',   '5',      false, false],
            ['511000', 'BEBAN BUNGA JASA DAN SIMPANAN',                      'EXPENSE',   '510000', false, false],
            ['511100', 'BEBAN BUNGA TABUNGAN',                               'EXPENSE',   '511000', true,  false],
            ['511200', 'BEBAN BUNGA SIMPANAN BERJANGKA',                     'EXPENSE',   '511000', true,  false],
            ['511300', 'BEBAN BUNGA PINJAMAN BANK',                          'EXPENSE',   '511000', true,  false],
            ['511400', 'BEBAN BUNGA PINJAMAN KOPERASI LAIN',                 'EXPENSE',   '511000', true,  false],
            ['512000', 'BEBAN CKPN (CADANGAN KERUGIAN)',                     'EXPENSE',   '510000', false, false],
            ['512100', 'BEBAN PENYISIHAN KERUGIAN PINJAMAN',                 'EXPENSE',   '512000', true,  false],
            ['512200', 'BEBAN PENGHAPUSAN PINJAMAN',                         'EXPENSE',   '512000', true,  false],
            ['513000', 'BEBAN GAJI DAN TUNJANGAN KARYAWAN',                  'EXPENSE',   '510000', false, false],
            ['513100', 'BEBAN GAJI & TUNJANGAN KARYAWAN',                    'EXPENSE',   '513000', true,  false],
            ['513200', 'BEBAN LEMBUR',                                       'EXPENSE',   '513000', true,  false],
            ['513300', 'BEBAN BPJS KETENAGAKERJAAN & KESEHATAN',             'EXPENSE',   '513000', true,  false],
            ['513400', 'BEBAN THR & BONUS',                                  'EXPENSE',   '513000', true,  false],
            ['513500', 'BEBAN IMBALAN PASCA-KERJA',                          'EXPENSE',   '513000', true,  false],
            ['513600', 'BEBAN PELATIHAN & PENGEMBANGAN SDM',                 'EXPENSE',   '513000', true,  false],
            ['513700', 'BEBAN HONOR PENGURUS & PENGAWAS',                    'EXPENSE',   '513000', true,  false],
            ['514000', 'BEBAN OPERASIONAL KANTOR',                           'EXPENSE',   '510000', false, false],
            ['514100', 'BEBAN LISTRIK, AIR, DAN TELEPON',                    'EXPENSE',   '514000', true,  false],
            ['514200', 'BEBAN ATK DAN PERLENGKAPAN KANTOR',                  'EXPENSE',   '514000', true,  false],
            ['514300', 'BEBAN PERJALANAN DINAS',                             'EXPENSE',   '514000', true,  false],
            ['514400', 'BEBAN KONSUMSI DAN RAPAT',                           'EXPENSE',   '514000', true,  false],
            ['514500', 'BEBAN SEWA KANTOR',                                  'EXPENSE',   '514000', true,  false],
            ['514600', 'BEBAN CETAKAN & FOTOKOPI',                           'EXPENSE',   '514000', true,  false],
            ['514700', 'BEBAN KONSULTAN & JASA PROFESIONAL',                 'EXPENSE',   '514000', true,  false],
            ['514800', 'BEBAN IURAN & KEANGGOTAAN',                          'EXPENSE',   '514000', true,  false],
            ['514900', 'BEBAN KANTOR LAIN-LAIN',                             'EXPENSE',   '514000', true,  false],
            ['515000', 'BEBAN PENYUSUTAN DAN AMORTISASI',                    'EXPENSE',   '510000', false, false],
            ['515100', 'BEBAN PENYUSUTAN BANGUNAN',                          'EXPENSE',   '515000', true,  false],
            ['515200', 'BEBAN PENYUSUTAN KENDARAAN',                         'EXPENSE',   '515000', true,  false],
            ['515300', 'BEBAN PENYUSUTAN PERALATAN KANTOR',                  'EXPENSE',   '515000', true,  false],
            ['515400', 'BEBAN AMORTISASI ASET TAK BERWUJUD',                 'EXPENSE',   '515000', true,  false],
            ['516000', 'BEBAN PEMELIHARAAN DAN PERBAIKAN',                   'EXPENSE',   '510000', true,  false],
            ['517000', 'BEBAN ASURANSI',                                     'EXPENSE',   '510000', true,  false],
            ['518000', 'BEBAN PEMASARAN DAN PROMOSI',                        'EXPENSE',   '510000', true,  false],

            ['520000', 'BEBAN NON OPERASIONAL',                              'EXPENSE',   '5',      false, false],
            ['521000', 'BEBAN ADMINISTRASI BANK',                            'EXPENSE',   '520000', true,  false],
            ['522000', 'BEBAN PAJAK BUNGA SIMPANAN',                         'EXPENSE',   '520000', true,  false],
            ['523000', 'BEBAN PAJAK PENGHASILAN BADAN',                      'EXPENSE',   '520000', false, false],
            ['523100', 'BEBAN PAJAK PENGHASILAN BADAN',                      'EXPENSE',   '523000', true,  false],
            ['523200', 'BEBAN TAKSIRAN PAJAK KINI (PPH BADAN)',               'EXPENSE',   '523000', true,  false],
            // Catatan: kode 523000 duplikat di sumber data, dikoreksi ke 524000
            ['524000', 'BEBAN LAIN-LAIN NON OPERASIONAL',                    'EXPENSE',   '520000', true,  false],
        ];

        // ─────────────────────────────────────────────────────────────────────
        // INSERT — Sequential (respek hierarki parent)
        // ─────────────────────────────────────────────────────────────────────
        $inserted = []; // [coa_code => id]

        foreach ($coas as [$code, $name, $type, $parentCode, $isLeaf, $isCash]) {
            $parentId = null;
            if ($parentCode !== null) {
                $parentId = $inserted[$parentCode] ?? null;
            }

            $coa = Coa::create([
                'coa_code'  => $code,
                'name'      => $name,
                'type'      => $type,
                'parent_id' => $parentId,
                'is_leaf'   => $isLeaf,
                'is_cash'   => $isCash,
                'is_active' => true,
            ]);

            $inserted[$code] = $coa->id;
        }

        $this->command->info('✅ Sirara Apps COA seeded — ' . Coa::count() . ' akun berhasil dibuat.');
    }
}
