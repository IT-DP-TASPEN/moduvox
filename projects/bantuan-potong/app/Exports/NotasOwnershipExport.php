<?php

namespace App\Exports;

use App\Models\NotasOwnership;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class NotasOwnershipExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithCustomValueBinder
{
    private $rowNumber = 0;

    public function collection()
    {
        return NotasOwnership::with('mitra')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Mitra',
            'Notas',
            'Nama Nasabah',
            'Rekening Tabungan',
            'Rekening Replace',
        ];
    }

    public function map($notasOwnership): array
    {
        return [
            ++$this->rowNumber,
            $notasOwnership->mitra->nama_mitra ?? '-',
            $notasOwnership->notas,
            $notasOwnership->nama_nasabah,
            $notasOwnership->rek_tabungan,
            $notasOwnership->rek_replace,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        // Force semua value jadi string/text
        $cell->setValueExplicit($value, DataType::TYPE_STRING);
        return true;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 30,
            'C' => 20,
            'D' => 30,
            'E' => 20,
            'F' => 20,
        ];
    }
}
