<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class BanpotMasterExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithCustomValueBinder
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Rekening Tabungan',
            'Nama Nasabah',
            'NOTAS',
            'Rekening Kredit',
            'Tenor',
            'Angsuran Ke',
            'TMT Kredit',
            'TAT Kredit',
            'Gaji Pensiun',
            'Nominal Potongan',
            'Bank Transfer',
            'Rekening Transfer',
            'Saldo Mengendap',
            'Jumlah Tertagih',
            'Fee Banpot',
            'Rek Tabungan Valid',
            'NOTAS Valid',
            'DAPEM Valid',
            'OTEN Valid',
            'Final Validasi Status',
            'Bulan Dapem',
            'Keterangan',
            'Status Banpot',
            'Created Mitra',
            'Created By',
            'Created At',
            'Updated At',
        ];
    }

    public function map($row): array
    {
        return [
            $row->rek_tabungan, // Biarkan pure, bindValue yang akan menangani formatnya
            $row->nama_nasabah,
            $row->notas,
            $row->rek_kredit,
            $row->tenor,
            $row->angsuran_ke,
            $row->tmt_kredit,
            $row->tat_kredit,
            $row->gaji_pensiun,
            $row->nominal_potongan,
            $row->bank_transfer,
            $row->rek_transfer,
            $row->saldo_mengendap,
            $row->jumlah_tertagih,
            $row->fee_banpot,
            $this->formatValidasi($row->rek_tabungan_valid),
            $this->formatValidasi($row->notas_valid),
            $this->formatValidasi($row->dapem_valid),
            $this->formatValidasi($row->oten_valid),
            $this->formatValidasi($row->final_validasi_status),
            $row->bulan_dapem,
            $row->keterangan,
            ucfirst(str_replace('_', ' ', $row->status_banpot)),
            $row->created_mitra,
            optional($row->creator)->name ?? '-',
            $row->created_at?->format('Y-m-d H:i:s'),
            $row->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Rekening Tabungan
            'C' => NumberFormat::FORMAT_TEXT, // NOTAS
            'D' => NumberFormat::FORMAT_TEXT, // Rekening Kredit
            'L' => NumberFormat::FORMAT_TEXT, // Rekening Transfer
        ];
    }

    /**
     * Menangani force format string untuk angka yang panjang (>15 digit)
     * agar tidak dibulatkan otomatis oleh Excel menjadi nol di ujungnya.
     */
    public function bindValue(Cell $cell, $value)
    {
        // Daftar kolom yang berisi angka panjang dan harus tetap string
        $textColumns = ['A', 'C', 'D', 'L'];

        if (in_array($cell->getColumn(), $textColumns)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        // Untuk kolom lain, gunakan binder bawaan
        return parent::bindValue($cell, $value);
    }

    protected function formatValidasi($value)
    {
        if ($value === true || $value === 1 || $value === '1') {
            return 'Valid';
        }
        if ($value === false || $value === 0 || $value === '0') {
            return 'Tidak Valid';
        }
        return '-';
    }
}