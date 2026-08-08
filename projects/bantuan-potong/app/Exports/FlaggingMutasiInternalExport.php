<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FlaggingMutasiInternalExport implements FromCollection, WithMapping, WithHeadings, WithEvents
{
    protected $records;
    protected $num = 0;

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
            'No',
            'Wilayah',
            'Nama Nasabah',
            'Notas',
            'NIK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'No Handphone',
            'Rek Tabungan',
            'Rek Kredit',
            'TMT Kredit',
            'TAT Kredit',
            'KTP',
            'SP Deb/Flagging',
            'Foto Tab',
            'Form Pindah Kantor',
            'Fee',
            'Fee Checking',
            'Status',
            'Keterangan',
            'Bukti Hasil',
            'Created Mitra',
            'Created By',
            'Created At',
            'Updated At',
        ];
    }

    public function map($row): array
    {
        $this->num++;

        return [
            $this->num,
            $row->wilayah,
            $row->nama_nasabah,
            $row->notas,
            $row->nik,
            $row->tempat_lahir,
            $row->tanggal_lahir ? Carbon::parse($row->tanggal_lahir)->format('Y-m-d') : '',
            $row->alamat,
            $row->no_handphone,
            $row->rek_tabungan,
            $row->rek_kredit,
            $row->tmt_kredit ? Carbon::parse($row->tmt_kredit)->format('Y-m-d') : '',
            $row->tat_kredit ? Carbon::parse($row->tat_kredit)->format('Y-m-d') : '',
            $row->ktp,
            $row->sp_deb_flagging,
            $row->foto_tab,
            $row->form_pindah_kantor,
            $row->fee,
            $row->fee_checking,
            ucfirst(str_replace('_', ' ', $row->status)),
            $row->keterangan,
            $row->bukti_hasil,
            $row->created_mitra,
            $row->creator->name ?? '',
            $row->created_at?->format('Y-m-d H:i:s'),
            $row->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Kolom yang harus diformat sebagai teks supaya tidak diubah Excel
                $textCols = ['D', 'E', 'I', 'J', 'K'];

                foreach ($textCols as $col) {
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $cell = $sheet->getCell("{$col}{$row}");
                        $cell->setValueExplicit($cell->getValue(), DataType::TYPE_STRING);
                    }
                }
            },
        ];
    }
}
