<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithHeadings;


class CheckingInternalExport implements FromCollection, WithMapping, WithHeadings
{
    protected $records;
    protected $rowNumber = 0;

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
            'No Taspen',
            'Lampiran',
            'Fee',
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
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->wilayah,
            $row->nama_nasabah,
            $row->notas,
            $row->lampiran,
            $row->fee,
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
                $textCols = ['D'];

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
