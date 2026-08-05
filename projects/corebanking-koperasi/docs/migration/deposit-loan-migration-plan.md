# Deposit and Loan Migration Plan

Dokumen ini adalah baseline persiapan migrasi data deposito dan kredit ke sistem New Gen Sirara.

Asumsi sumber data: data deposito dan kredit awal berasal dari file Excel, bukan dari database sistem lama. Karena itu proses migrasi harus dimulai dari normalisasi Excel ke format template/staging.

Penjelasan setiap kolom template tersedia di [template-column-guide.md](template-column-guide.md).

## Prinsip Utama

Migrasi deposito dan kredit harus berbasis posisi cut-off. Jangan memanggil service operasional seperti `DepositOperationService::openAccount()` atau `LoanOperationService::disburseLoan()` untuk data historis, karena service tersebut membuat jurnal, transaksi, dan mutasi tabungan baru.

Gunakan alur:

```text
Excel source
  -> CSV/template normalization
  -> raw import
  -> staging
  -> validation
  -> production tables
  -> opening/cut-off journal
  -> reconciliation
```

## Cut-Off

Tetapkan satu tanggal cut-off resmi, misalnya:

```text
2026-06-24 23:59:59
```

Semua saldo, outstanding, bunga, denda, status rekening, DPD, KOL, dan jadwal harus merepresentasikan posisi pada tanggal cut-off tersebut.

## Urutan Migrasi

1. `branches`
2. `marketing_masters`
3. `coas`
4. `saving_products`
5. `deposit_products`
6. `loan_products`
7. `cifs`
8. `saving_accounts`
9. `saving_blocks`
10. `deposit_bilyets`
11. `deposit_accounts`
12. `deposit_schedules`
13. `deposit_transactions` posisi awal
14. `loan_accounts`
15. `loan_schedules`
16. `loan_transactions` posisi awal
17. `loan_documents`, jika dokumen lama tersedia
18. Opening/cut-off journals
19. Reconciliation

## Mapping Wajib

Karena sumbernya Excel, mapping dilakukan dari kode/nomor/nama di Excel ke ID database sistem baru:

| Mapping | Tujuan |
| --- | --- |
| Kolom Excel / Template | Tujuan |
| --- | --- |
| `cif_no` | Dicari ke `cifs.id` |
| `saving_account_no` | Dicari ke `saving_accounts.id` |
| `deposit_product_code` atau nama produk | Dicari ke `deposit_products.id` |
| `loan_product_code` atau nama produk | Dicari ke `loan_products.id` |
| `branch_code` atau nama cabang | Dicari ke `branches.id` |
| `marketing_code` atau nama marketing | Dicari ke `marketing_masters.id` |
| `bilyet_number` | Dicari/dibuat ke `deposit_bilyets.id` |

Jika Excel belum punya kode yang stabil, tambahkan kolom bantu di Excel sebelum import. Jangan mengandalkan nama nasabah saja untuk mapping karena rawan duplikat.

## Excel Source Rules

- Setiap sheet harus punya header yang konsisten.
- Jangan pakai merged cells.
- Jangan pakai subtotal di tengah data.
- Format nominal harus angka murni, bukan teks seperti `Rp 1.000.000`.
- Format tanggal harus valid dan konsisten.
- Satu baris Excel harus merepresentasikan satu rekening atau satu jadwal.
- Tambahkan kolom `source_row_number` saat import ke staging untuk memudahkan koreksi error.
- Kolom `source_system` bisa diisi `excel_migration`.
- Kolom `source_id` bisa diisi nomor rekening dari Excel atau gabungan nama sheet + nomor baris.

## Deposito: Data Wajib

Target utama: `deposit_accounts`, `deposit_schedules`, `deposit_transactions`.

Field minimal:

- `account_no`
- `cif_id`
- `deposit_product_id`
- `deposit_bilyet_id`
- `amount`
- `interest_rate`
- `tenor`
- `placement_date`
- `maturity_date`
- `rollover_type`: `NONE`, `PRINCIPAL`, `PRINCIPAL_INTEREST`
- `saving_account_id`
- `interest_calculation_type`: `MONTHLY` atau `DAILY`
- `branch_id`
- `marketing_id`
- `fund_channel`: `KAS`, `BANK`, atau hasil mapping channel
- `status`: `PENDING`, `ACTIVE`, `CLOSED`, `MATURED`

Validasi khusus:

- `maturity_date` harus sesuai `placement_date + tenor`, kecuali data lama punya aturan khusus.
- Deposito dengan bunga dibayar periodik wajib punya `saving_account_id`.
- Bilyet yang terpakai harus berstatus `USED`.
- Total `deposit_schedules.net_interest` harus cocok dengan perhitungan bunga net setelah pajak.
- Untuk migrasi awal ini, semua `deposit_schedules` diisi `PENDING` dulu dan `payment_date` dikosongkan.
- Jika nanti ada data bunga yang memang sudah dibayar dan ingin diakui sebagai historis, baris tersebut boleh diubah ke `PAID` dan wajib punya `payment_date`.

## Kredit: Data Wajib

Target utama: `loan_accounts`, `loan_schedules`, `loan_transactions`.

Field minimal:

- `account_no`
- `pk_number`
- `cif_id`
- `loan_product_id`
- `saving_account_id`
- `branch_id`
- `marketing_id`
- `principal_amount`
- `interest_rate`
- `interest_margin`
- `tenor`
- `tenor_type`
- `calculation_method`: `FLAT`, `EFFECTIVE`, `ANNUITY`
- `is_diskonto`
- `diskonto_upfront_amount`
- `due_date_cycle`
- `disbursement_date`
- `outstanding_principal`
- `outstanding_interest`
- `outstanding_penalty`
- `dpd_days`
- `kol_level`
- `status`: `PENDING`, `APPROVED`, `ACTIVE`, `CLOSED`, `NPL`, `CANCELLED`, `REJECTED`, `CLAIM_SUBMITTED`, `CLAIM_APPROVED`
- biaya: `provision_fee`, `admin_fee`, `insurance_fee`, `flagging_fee`, `stamp_duty_fee`
- dana mengendap: `blocked_savings_count`, `blocked_savings_amount`
- angsuran dibayar di muka: `prepaid_installment_count`, `prepaid_installment_amount`

Validasi khusus:

- `outstanding_principal` harus sama dengan sisa `principal_amount - principal_paid` dari `loan_schedules`.
- `outstanding_interest` harus sama dengan sisa `interest_amount - interest_paid`.
- `outstanding_penalty` harus sama dengan sisa `penalty_amount - penalty_paid`.
- `kol_level` harus konsisten dengan `dpd_days`.
- `status` harus `NPL` jika KOL 3-5 dan masih ada outstanding.
- Kredit lunas harus `CLOSED` dan outstanding semua nol.
- Kredit dalam proses klaim memakai `CLAIM_SUBMITTED` atau `CLAIM_APPROVED`.
- Untuk diskonto, `calculation_method` harus `FLAT`.

## Staging Tables

Disarankan membuat tabel staging terpisah:

- `migration_batches`
- `migration_mappings`
- `migration_errors`
- `staging_deposit_accounts`
- `staging_deposit_schedules`
- `staging_loan_accounts`
- `staging_loan_schedules`

Kolom kontrol minimal untuk semua staging:

- `batch_id`
- `source_system`
- `source_id`
- `source_row_number`
- `validation_status`: `PENDING`, `VALID`, `INVALID`, `IMPORTED`
- `validation_errors`
- `imported_id`
- `created_at`
- `updated_at`

## Rekonsiliasi

Deposito:

- total nominal deposito aktif
- jumlah rekening aktif
- jumlah rekening jatuh tempo
- total bunga gross
- total pajak bunga
- total bunga net
- total per produk
- total per cabang
- total per `liability_coa_id` produk deposito
- total jurnal migrasi deposito: debit akun penyeimbang migrasi sama dengan credit COA simpanan berjangka

Kredit:

- total plafon
- total outstanding pokok
- total outstanding bunga
- total outstanding denda
- jumlah rekening aktif
- jumlah rekening NPL
- jumlah rekening lunas
- total per KOL
- total per produk
- total per cabang

## Jurnal Saldo Awal Deposito

Untuk migrasi awal, jangan memakai jurnal penempatan operasional:

```text
Dr Kas/Bank
Cr Simpanan Berjangka
```

Karena saldo ini bukan transaksi uang masuk hari ini, melainkan saldo historis pada tanggal cut-off.

Gunakan jurnal cut-off khusus:

```text
Dr COA Penyeimbang Migrasi / Saldo Awal Migrasi
Cr COA Simpanan Berjangka dari produk deposito
```

Contoh:

```text
Dr 149999 - Rekening Antara Migrasi / Saldo Awal
Cr 212010 - Simpanan Deposito Berjangka
```

Catatan:

- COA debit harus ditentukan dan disetujui oleh accounting sebelum import final.
- COA debit tidak mewakili kas/bank transaksi operasional.
- Credit wajib memakai `deposit_products.liability_coa_id`.
- Jurnal bisa dibuat satu per batch, dengan detail credit dipecah per produk, cabang, dan COA.
- Total credit harus sama dengan total `deposit_accounts.amount` yang berstatus `ACTIVE` atau `MATURED` pada cut-off.
- Akun penyeimbang migrasi harus selesai/terjelaskan dalam rekonsiliasi neraca awal bersama modul lain seperti kredit, kas/bank, tabungan, dan modal.

Format detail neraca import deposito tersedia di [deposit_opening_journal_template.csv](deposit_opening_journal_template.csv).

## Import Deposito

Gunakan command khusus migrasi deposito agar data historis tidak melewati flow operasional placement:

```bash
php artisan migration:import-deposits
```

Tanpa opsi tambahan, command berjalan sebagai dry-run:

- membaca `docs/migration/deposit_accounts_template.csv`
- membaca `docs/migration/deposit_schedules_template.csv`
- validasi foreign key, COA opening, duplicate rekening, dan schedule
- menampilkan total pokok dan total bunga
- tidak insert data

Jika dry-run sudah valid, import final dijalankan dengan:

```bash
php artisan migration:import-deposits --commit
```

Command ini akan:

- membuat `deposit_accounts`
- membuat `deposit_transactions` tipe `PLACEMENT` dengan channel `INTERNAL` sebagai jejak migrasi
- membuat `deposit_schedules` sesuai template
- membuat jurnal opening deposito:

```text
Dr opening_debit_coa_code
Cr opening_credit_coa_code
```

Untuk default migrasi saat ini, schedule bunga deposito dibuat `PENDING` dulu. Command tetap tidak membuat jurnal bunga lama saat import; jurnal bunga baru muncul saat proses pembayaran bunga dijalankan dari flow deposito.

## Import Kredit

Gunakan command khusus migrasi kredit agar data historis tidak melewati flow operasional disbursement:

```bash
php artisan migration:import-loans --opening-credit-coa=149999
```

Jika `loan_schedules_template.csv` masih hanya berisi header, command akan membuat schedule otomatis dari `loan_accounts_template.csv` untuk kebutuhan import. Setelah dry-run valid, import final dijalankan dengan:

```bash
php artisan migration:import-loans --opening-credit-coa=149999 --commit
```

Command ini akan membuat `loan_accounts`, `loan_transactions` tipe `DISBURSEMENT` sebagai jejak migrasi, `loan_schedules`, dan jurnal opening kredit:

```text
Dr loan_products.principal_coa_id
Cr opening-credit-coa / COA penyeimbang migrasi
```

Schedule kredit hasil generate migrasi selalu dibuat `UNPAID`, dengan nilai tagihan mengikuti outstanding cut-off dan seluruh kolom paid bernilai nol.

## Go-Live Checklist

- Cut-off disetujui.
- Sistem lama freeze atau delta migration disiapkan.
- Semua mapping master selesai.
- Semua staging valid.
- Tidak ada foreign key kosong untuk data wajib.
- Tidak ada duplicate `account_no`, `pk_number`, `bilyet_number`, `reference_number`.
- Reconciliation source vs target selisih nol atau ada berita acara.
- Backup database sebelum import final.
- Import final dijalankan di maintenance window.
- Post-migration reconciliation ditandatangani.
- Auto command seperti maturity, pay-interest, auto-debit, dan recalculate KOL dijalankan setelah data tervalidasi.
