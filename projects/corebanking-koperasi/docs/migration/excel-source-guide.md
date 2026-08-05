# Excel Source Migration Guide

Panduan ini dipakai jika data awal deposito dan kredit hanya tersedia dalam Excel.

## Workbook Yang Disarankan

Buat satu workbook dengan sheet berikut:

1. `cif_mapping`
2. `saving_mapping`
3. `deposit_accounts`
4. `deposit_schedules`
5. `loan_accounts`
6. `loan_schedules`
7. `validation_notes`

## Prinsip

Excel dianggap sebagai raw source, bukan final truth yang langsung masuk production table.

Alurnya:

```text
Excel
  -> template CSV
  -> staging table
  -> validation report
  -> correction in Excel/template
  -> import final
```

## Kolom Tambahan Untuk Excel

Tambahkan kolom bantu ini saat import dari Excel:

| Kolom | Fungsi |
| --- | --- |
| `source_file` | Nama file Excel sumber |
| `source_sheet` | Nama sheet |
| `source_row_number` | Nomor baris Excel |
| `source_id` | ID unik baris, bisa nomor rekening/PK/bilyet |
| `validation_status` | `PENDING`, `VALID`, `INVALID`, `IMPORTED` |
| `validation_errors` | Daftar error validasi |

## Deposit Excel Minimum

Untuk sheet `deposit_accounts`, minimal wajib ada:

- nomor deposito
- nomor CIF
- kode/nama produk deposito
- nominal pokok
- bunga
- tenor
- tanggal penempatan
- tanggal jatuh tempo
- tipe rollover
- nomor rekening tabungan pembayaran bunga
- kode/nama cabang
- status

Untuk sheet `deposit_schedules`, minimal wajib ada:

- nomor deposito
- periode/bulan ke
- tanggal jadwal bunga
- bunga gross
- pajak
- bunga net
- status pembayaran
- tanggal bayar, jika sudah dibayar

## Loan Excel Minimum

Untuk sheet `loan_accounts`, minimal wajib ada:

- nomor kredit
- nomor PK
- nomor CIF
- kode/nama produk kredit
- nomor rekening tabungan auto-debet/pencairan
- plafon
- outstanding pokok
- outstanding bunga
- outstanding denda
- bunga
- tenor
- metode hitung
- tanggal pencairan
- tanggal jatuh tempo angsuran
- DPD
- KOL
- status

Untuk sheet `loan_schedules`, minimal wajib ada:

- nomor kredit
- nomor angsuran
- tanggal jatuh tempo
- pokok tagihan
- bunga tagihan
- denda tagihan
- pokok terbayar
- bunga terbayar
- denda terbayar
- status angsuran
- tanggal bayar, jika ada

## Validasi Yang Harus Dilakukan

- Nomor rekening tidak duplikat.
- Nomor CIF di Excel ditemukan di sistem.
- Produk di Excel ditemukan di sistem.
- Cabang di Excel ditemukan di sistem.
- Tabungan bunga deposito ditemukan dan milik CIF yang sama.
- Tabungan auto-debet kredit ditemukan dan milik CIF yang sama.
- Total outstanding kredit sama dengan sisa jadwal.
- Jadwal deposito sesuai tenor.
- Status rekening dan jadwal valid.
- Tidak ada nominal negatif kecuali memang reversal dan disepakati.

## Catatan Penting

Jika CIF atau rekening tabungan juga belum ada di sistem, migrasikan CIF dan tabungan lebih dulu. Deposito dan kredit sebaiknya tidak diimport sebelum relasi nasabah dan rekening dasar selesai.
