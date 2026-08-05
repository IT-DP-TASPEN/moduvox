# Migration Validation Checklist

## Master Data

- Semua `cif_no` unik.
- Semua `nik` unik atau ada daftar exception yang disetujui.
- Semua rekening tabungan punya CIF valid.
- Semua produk deposito dan kredit sudah punya COA wajib.
- Semua branch dan marketing dari source punya mapping.

## Deposito

- Tidak ada `account_no` deposito duplikat.
- Semua `cif_id` valid.
- Semua `deposit_product_id` valid.
- Semua `saving_account_id` valid untuk deposito dengan pembayaran bunga internal.
- Semua `amount > 0`.
- Semua `interest_rate` berada dalam range produk, atau masuk daftar override.
- Semua `rollover_type` valid.
- Semua `status` valid.
- Total deposito aktif per source sama dengan target.
- Total bunga pending per source sama dengan target.
- Jumlah schedule per deposito sesuai tenor.

## Kredit

- Tidak ada `account_no` kredit duplikat.
- Tidak ada `pk_number` duplikat jika tidak null.
- Semua `cif_id` valid.
- Semua `loan_product_id` valid.
- Semua `saving_account_id` valid untuk kredit yang auto-debet atau pencairan internal.
- Semua `principal_amount > 0`.
- Semua `outstanding_principal >= 0`.
- Semua `outstanding_interest >= 0`.
- Semua `outstanding_penalty >= 0`.
- Outstanding account sama dengan residual schedule.
- `kol_level` konsisten dengan `dpd_days`.
- Kredit lunas punya status `CLOSED`.
- Kredit KOL 3-5 dengan outstanding punya status `NPL`.
- Kredit diskonto memakai `calculation_method = FLAT`.

## Accounting

- Opening/cut-off journal balance: total debit sama dengan total credit.
- Total deposito aktif sama dengan saldo COA simpanan berjangka terkait.
- Jurnal migrasi deposito tidak memakai debit kas/bank operasional.
- Debit jurnal migrasi deposito memakai COA penyeimbang migrasi/cut-off yang disetujui accounting.
- Credit jurnal migrasi deposito memakai `deposit_products.liability_coa_id`.
- Detail credit jurnal deposito dapat ditelusuri per batch, produk, cabang, dan COA.
- Total outstanding pokok kredit sama dengan saldo COA piutang kredit terkait.
- Total bunga/denda outstanding cocok dengan COA terkait jika dibukukan.
- Tidak ada transaksi historis yang menggandakan saldo tabungan.

## Go-Live

- Backup database dibuat.
- Import final memakai batch ID.
- Semua error staging terselesaikan.
- Reconciliation report disimpan.
- Approval/berita acara migrasi ditandatangani.
- Scheduler/command otomatis diaktifkan setelah validasi final.
