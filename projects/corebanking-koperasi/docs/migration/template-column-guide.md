# Migration Template Column Guide

Panduan ini menjelaskan isi setiap kolom pada template CSV migrasi.

CSV template sengaja hanya berisi header agar aman dipakai untuk import. Penjelasan kolom ditempatkan di dokumen ini supaya baris komentar tidak ikut terbaca sebagai data.

## Aturan Umum

- Format tanggal gunakan `YYYY-MM-DD`, contoh `2026-06-24`.
- Format tanggal dan jam gunakan `YYYY-MM-DD HH:MM:SS`, contoh `2026-06-24 23:59:59`.
- Nominal uang gunakan angka tanpa pemisah ribuan, contoh `15000000.00`.
- Persentase bunga gunakan angka persen, contoh `6.50` untuk 6,5%.
- Kolom sumber seperti `cif_no`, `deposit_account_no`, `loan_account_no`, `product_code`, dan `branch_code` diisi dari Excel untuk kebutuhan mapping dan audit.
- Kolom `*_id` diisi ID dari database sistem baru setelah mapping selesai.
- Jika nilai belum diketahui saat raw import, kolom `*_id` boleh kosong dulu, lalu diisi saat proses mapping staging.
- Untuk sumber Excel, `source_system` disarankan diisi `excel_migration`.
- Untuk sumber Excel, `source_id` bisa diisi nomor rekening, nomor bilyet, nomor PK, atau kombinasi nama sheet + nomor baris.
- `source_file`, `source_sheet`, dan `source_row_number` dipakai untuk melacak baris asal dari Excel saat validasi error.
- `migration_notes` boleh diisi catatan manual, misalnya "tanggal jatuh tempo dikoreksi dari Excel".

## deposit_accounts_template.csv

| Kolom | Diisi Dengan | Contoh | Catatan |
| --- | --- | --- | --- |
| `source_system` | Nama sistem/sumber data lama | `legacy_core` | Wajib untuk audit batch migrasi. |
| `source_file` | Nama file Excel sumber | `migrasi_deposito.xlsx` | Boleh dikosongkan kalau hanya satu file. |
| `source_sheet` | Nama sheet Excel sumber | `deposit_accounts` | Membantu trace error validasi. |
| `source_row_number` | Nomor baris di Excel | `12` | Membantu koreksi cepat di file Excel. |
| `source_id` | ID unik record di sistem lama | `DEP-000123` | Bisa berupa ID tabel lama atau nomor rekening lama. |
| `deposit_account_no` | Nomor deposito dari Excel | `D001234` | Dipakai untuk mapping dan trace back. |
| `account_no` | Nomor rekening deposito di sistem baru | `DEP00100000000001` | Harus unik. Boleh sama dengan nomor lama jika format diterima. |
| `cif_no` | Nomor CIF dari Excel | `00010000001` | Dipakai untuk mencari `cif_id`. |
| `cif_id` | ID CIF di sistem baru | `1` | Wajib sebelum import final ke `deposit_accounts`. |
| `deposit_product_code` | Kode produk deposito dari Excel | `DEP3M` | Dipakai untuk mapping produk. |
| `deposit_product_id` | ID produk deposito sistem baru | `1` | Wajib. |
| `bilyet_number` | Nomor bilyet dari Excel | `BLY-001234` | Kosong jika tidak ada bilyet. |
| `deposit_bilyet_id` | ID bilyet di sistem baru | `12` | Kosong jika tidak dimigrasikan. |
| `amount` | Pokok deposito posisi cut-off | `10000000.00` | Wajib, harus lebih dari 0 untuk deposito aktif. |
| `interest_rate` | Suku bunga tahunan | `6.50` | Dalam persen, bukan decimal `0.065`. |
| `tenor` | Tenor deposito | `12` | Saat ini sistem membaca tenor sebagai bulan. |
| `placement_date` | Tanggal penempatan | `2026-01-01` | Wajib. |
| `maturity_date` | Tanggal jatuh tempo | `2027-01-01` | Harus konsisten dengan tenor kecuali ada exception. |
| `rollover_type` | Jenis ARO/rollover | `NONE` | Nilai valid: `NONE`, `PRINCIPAL`, `PRINCIPAL_INTEREST`. |
| `saving_account_no` | Rekening tabungan dari Excel untuk pembayaran bunga/pokok | `101000100000000001` | Dipakai untuk mapping `saving_account_id`. |
| `saving_account_id` | ID rekening tabungan sistem baru | `10` | Wajib jika bunga/pokok dibayar internal. |
| `interest_calculation_type` | Metode hitung bunga | `MONTHLY` | Umumnya `MONTHLY` atau `DAILY`. |
| `branch_code` | Kode cabang dari Excel | `001` | Dipakai untuk mapping cabang. |
| `branch_id` | ID cabang sistem baru | `1` | Wajib. |
| `marketing_code` | Kode AO/marketing dari Excel | `AO001` | Kosong jika tidak ada. |
| `marketing_id` | ID marketing sistem baru | `1` | Boleh kosong. |
| `source_of_funds` | Sumber dana nasabah | `Gaji` | Boleh kosong. |
| `fund_channel` | Channel sumber dana | `BANK` | Sesuaikan enum sistem: migration awal memakai `KAS` atau `BANK`; operasional service memakai normalisasi channel. |
| `reason` | Keterangan migrasi/rekening | `Migrasi cut-off 2026-06-24` | Boleh kosong, disarankan diisi. |
| `status` | Status deposito | `ACTIVE` | Nilai valid: `PENDING`, `ACTIVE`, `CLOSED`, `MATURED`. |
| `closed_at` | Tanggal tutup deposito | `2026-06-24 10:00:00` | Diisi hanya jika status `CLOSED` atau `MATURED`. |
| `created_by` | ID user pembuat/system user | `3` | Wajib karena tabel `deposit_accounts.created_by` tidak nullable. |
| `migration_notes` | Catatan manual migrasi | `Data dari Excel marketing Juni` | Boleh kosong. |
| `migration_batch_code` | Kode batch migrasi | `DEP-MIG-2026-06-24` | Dipakai untuk mengelompokkan rekening deposito dengan jurnal saldo awal. |
| `opening_journal_reference` | Nomor referensi jurnal saldo awal | `MIG-DEP-20260624` | Sama untuk baris yang masuk jurnal opening deposito yang sama. |
| `opening_debit_coa_code` | Kode COA debit penyeimbang migrasi | `149999` | Bukan kas/bank operasional; harus disetujui accounting. |
| `opening_debit_coa_id` | ID COA debit penyeimbang migrasi | `99` | Boleh kosong saat raw import, diisi setelah mapping COA. |
| `opening_credit_coa_code` | Kode COA credit simpanan berjangka | `212010` | Biasanya mengikuti `deposit_products.liability_coa_id`. |
| `opening_credit_coa_id` | ID COA credit simpanan berjangka | `25` | Boleh kosong saat raw import, diisi setelah mapping produk/COA. |

Catatan jurnal saldo awal deposito:

- Import rekening deposito tidak memakai debit kas/bank operasional.
- Jurnal opening harus tetap balance: total `opening_debit_coa_*` sama dengan total `opening_credit_coa_*`.
- Detail jurnal dapat digroup dari template ini berdasarkan `migration_batch_code`, `branch_code`, `deposit_product_code`, dan `opening_credit_coa_code`.
- Jika semua produk deposito memakai COA liability yang sama, `opening_credit_coa_code` boleh sama di semua baris.

## deposit_schedules_template.csv

| Kolom | Diisi Dengan | Contoh | Catatan |
| --- | --- | --- | --- |
| `source_system` | Nama sistem/sumber data lama | `legacy_core` | Untuk audit. |
| `source_file` | Nama file Excel sumber | `migrasi_deposito.xlsx` | Boleh kosong jika tidak perlu. |
| `source_sheet` | Nama sheet Excel sumber | `deposit_schedules` | Membantu trace error. |
| `source_row_number` | Nomor baris di Excel | `25` | Membantu koreksi cepat. |
| `source_id` | ID unik jadwal di sistem lama | `DEP-SCH-0001` | Untuk trace back. |
| `deposit_account_no` | Nomor deposito dari Excel | `D001234` | Dipakai untuk mapping ke rekening deposito. |
| `deposit_account_id` | ID deposito sistem baru | `25` | Diisi setelah `deposit_accounts` berhasil dimapping/import. |
| `month_index` | Urutan bulan/periode bunga | `1` | Mulai dari 1. |
| `schedule_date` | Tanggal pembayaran bunga | `2026-02-01` | Wajib. |
| `gross_interest` | Bunga kotor periode tersebut | `50000.00` | Sebelum pajak. |
| `tax_amount` | Pajak bunga periode tersebut | `10000.00` | Isi `0.00` jika tidak ada pajak. |
| `net_interest` | Bunga bersih | `40000.00` | `gross_interest - tax_amount`. |
| `status` | Status jadwal bunga | `PENDING` | Nilai umum: `PENDING`, `PAID`. |
| `payment_date` | Tanggal bunga dibayar | `2026-02-01 09:00:00` | Wajib jika status `PAID`; kosong jika `PENDING`. |
| `transaction_no` | Nomor transaksi pembayaran bunga dari Excel | `TRX-DEP-001` | Kosong jika belum dibayar. |
| `deposit_transaction_id` | ID transaksi deposito sistem baru | `88` | Biasanya kosong saat staging, diisi setelah transaksi posisi awal dibuat. |
| `migration_notes` | Catatan manual migrasi | `Bunga sudah dibayar sebelum cut-off` | Boleh kosong. |

## loan_accounts_template.csv

| Kolom | Diisi Dengan | Contoh | Catatan |
| --- | --- | --- | --- |
| `source_system` | Nama sistem/sumber data lama | `legacy_core` | Untuk audit. |
| `source_file` | Nama file Excel sumber | `migrasi_kredit.xlsx` | Boleh dikosongkan kalau hanya satu file. |
| `source_sheet` | Nama sheet Excel sumber | `loan_accounts` | Membantu trace error validasi. |
| `source_row_number` | Nomor baris di Excel | `14` | Membantu koreksi cepat di file Excel. |
| `source_id` | ID unik kredit di sistem lama | `LOAN-000123` | Bisa ID lama atau nomor rekening lama. |
| `loan_account_no` | Nomor kredit dari Excel | `KRD001234` | Untuk trace back. |
| `account_no` | Nomor rekening kredit sistem baru | `LON00100000000001` | Harus unik jika diisi. |
| `pk_number` | Nomor perjanjian kredit | `SIRARA/00000000000001/30062026` | Harus unik jika diisi. |
| `cif_no` | Nomor CIF dari Excel | `00010000001` | Untuk mapping nasabah. |
| `cif_id` | ID CIF sistem baru | `1` | Wajib. |
| `loan_product_code` | Kode produk kredit dari Excel | `KMG` | Untuk mapping produk. |
| `loan_product_id` | ID produk kredit sistem baru | `1` | Wajib. |
| `saving_account_no` | Rekening tabungan dari Excel untuk pencairan/auto-debet | `101000100000000001` | Kosong jika tidak ada rekening terkait. |
| `saving_account_id` | ID rekening tabungan sistem baru | `10` | Wajib untuk auto-debet atau pencairan internal. |
| `branch_code` | Kode cabang dari Excel | `001` | Untuk mapping cabang. |
| `branch_id` | ID cabang sistem baru | `1` | Wajib. |
| `marketing_code` | Kode AO/marketing dari Excel | `AO001` | Kosong jika tidak ada. |
| `marketing_id` | ID marketing sistem baru | `1` | Boleh kosong. |
| `principal_amount` | Plafon/pokok awal kredit | `50000000.00` | Nilai awal saat kredit dicairkan. |
| `interest_rate` | Suku bunga tahunan | `12.00` | Dalam persen. |
| `interest_margin` | Margin bunga tambahan | `0.00` | Isi `0.00` jika tidak dipakai. |
| `tenor` | Tenor kredit | `24` | Sesuai `tenor_type`. |
| `tenor_type` | Satuan tenor | `MONTHS` | Umumnya `MONTHS`; opsi lain tergantung produk. |
| `calculation_method` | Metode hitung angsuran | `FLAT` | Nilai umum: `FLAT`, `EFFECTIVE`, `ANNUITY`. |
| `is_diskonto` | Apakah kredit diskonto | `0` | Isi `1` untuk ya, `0` untuk tidak. |
| `diskonto_upfront_amount` | Bunga diskonto dibayar di muka | `0.00` | Wajib `0.00` jika bukan diskonto. |
| `due_date_cycle` | Tanggal jatuh tempo bulanan | `25` | Contoh `25` berarti jatuh tempo tiap tanggal 25. |
| `disbursement_date` | Tanggal pencairan | `2026-01-25` | Wajib untuk kredit aktif. |
| `outstanding_principal` | Sisa pokok cut-off | `35000000.00` | Harus cocok dengan residual jadwal. |
| `outstanding_interest` | Sisa bunga cut-off | `1200000.00` | Harus cocok dengan residual jadwal. |
| `outstanding_penalty` | Sisa denda cut-off | `0.00` | Isi `0.00` jika tidak ada denda. |
| `dpd_days` | Days past due | `0` | Hari keterlambatan pada cut-off. |
| `kol_level` | Kolektibilitas | `1` | 1 lancar, 2 DPK, 3 kurang lancar, 4 diragukan, 5 macet. |
| `collateral_type` | Jenis agunan | `BPKB` | Boleh kosong. |
| `collateral_description` | Deskripsi agunan | `Toyota Avanza 2020` | Boleh kosong. |
| `collateral_certificate_no` | Nomor sertifikat/dokumen agunan | `BPKB12345` | Boleh kosong. |
| `collateral_value` | Nilai agunan | `100000000.00` | Boleh kosong atau `0.00`. |
| `collateral_address` | Alamat objek agunan | `Bekasi` | Boleh kosong. |
| `guarantor_name` | Nama penjamin | `Budi` | Boleh kosong. |
| `guarantor_nik` | NIK penjamin | `3275...` | Boleh kosong. |
| `guarantor_phone` | Telepon penjamin | `08123456789` | Boleh kosong. |
| `guarantor_address` | Alamat penjamin | `Bekasi` | Boleh kosong. |
| `guarantor_relation` | Hubungan penjamin | `Saudara` | Boleh kosong. |
| `provision_fee` | Biaya provisi | `500000.00` | Isi `0.00` jika tidak ada. |
| `admin_fee` | Biaya admin | `100000.00` | Isi `0.00` jika tidak ada. |
| `insurance_fee` | Biaya asuransi | `250000.00` | Isi `0.00` jika tidak ada. |
| `flagging_fee` | Biaya flagging | `0.00` | Isi `0.00` jika tidak ada. |
| `stamp_duty_fee` | Biaya materai | `10000.00` | Isi `0.00` jika tidak ada. |
| `prepaid_installment_count` | Jumlah angsuran dibayar di muka | `0` | Isi `0` jika tidak ada. |
| `prepaid_installment_amount` | Total angsuran dibayar di muka | `0.00` | Harus cocok dengan jadwal jika count > 0. |
| `blocked_savings_count` | Jumlah angsuran dana mengendap | `0` | Isi `0` jika tidak ada. |
| `blocked_savings_amount` | Total dana mengendap | `0.00` | Harus cocok dengan rekening tabungan/block jika dipakai. |
| `status` | Status kredit | `ACTIVE` | Nilai valid: `PENDING`, `APPROVED`, `ACTIVE`, `CLOSED`, `NPL`, `CANCELLED`, `REJECTED`, `CLAIM_SUBMITTED`, `CLAIM_APPROVED`. |
| `approved_by` | ID user approver | `2` | Boleh kosong untuk data yang tidak disetujui. |
| `approved_at` | Tanggal approval | `2026-01-25 10:00:00` | Boleh kosong. |
| `created_by` | ID user pembuat/system user | `3` | Disarankan wajib untuk audit, walau kolom database nullable. |
| `migration_notes` | Catatan manual migrasi | `Outstanding sesuai laporan cut-off` | Boleh kosong. |

## loan_schedules_template.csv

| Kolom | Diisi Dengan | Contoh | Catatan |
| --- | --- | --- | --- |
| `source_system` | Nama sistem/sumber data lama | `legacy_core` | Untuk audit. |
| `source_file` | Nama file Excel sumber | `migrasi_kredit.xlsx` | Boleh kosong jika tidak perlu. |
| `source_sheet` | Nama sheet Excel sumber | `loan_schedules` | Membantu trace error. |
| `source_row_number` | Nomor baris di Excel | `33` | Membantu koreksi cepat. |
| `source_id` | ID unik jadwal kredit lama | `LOAN-SCH-0001` | Untuk trace back. |
| `loan_account_no` | Nomor kredit dari Excel | `KRD001234` | Dipakai untuk mapping ke kredit. |
| `loan_account_id` | ID kredit sistem baru | `40` | Diisi setelah `loan_accounts` dimapping/import. |
| `installment_number` | Nomor angsuran | `1` | Mulai dari 1. |
| `due_date` | Tanggal jatuh tempo angsuran | `2026-02-25` | Wajib. |
| `principal_amount` | Pokok angsuran | `2083333.33` | Nominal tagihan pokok periode ini. |
| `interest_amount` | Bunga angsuran | `500000.00` | Nominal tagihan bunga periode ini. |
| `penalty_amount` | Denda angsuran | `0.00` | Isi `0.00` jika tidak ada. |
| `principal_paid` | Pokok yang sudah dibayar | `2083333.33` | Tidak boleh melebihi `principal_amount`. |
| `interest_paid` | Bunga yang sudah dibayar | `500000.00` | Tidak boleh melebihi `interest_amount`. |
| `penalty_paid` | Denda yang sudah dibayar | `0.00` | Tidak boleh melebihi `penalty_amount`. |
| `status` | Status angsuran | `PAID` | Nilai valid: `UNPAID`, `PARTIAL`, `PAID`, `VOID`. |
| `payment_date` | Tanggal angsuran dibayar lunas/terakhir dibayar | `2026-02-25 09:00:00` | Wajib jika status `PAID`; boleh diisi untuk `PARTIAL`; kosong jika `UNPAID`. |
| `migration_notes` | Catatan manual migrasi | `Pembayaran parsial dari Excel` | Boleh kosong. |
