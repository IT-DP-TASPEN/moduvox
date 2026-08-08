<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class SavingAccountFincloudExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
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
            'customer_name',
            'national_id_number',
            'identity_type',
            'alternate_number',
            'mobile_phone',
            'place_of_birth',
            'date_of_birth',
            'gender',
            'religion',
            'mother_maiden_name',
            'address',
            'city',
            'urban_village',
            'sub_district',
            'postal_code',
            'province',
            'tax_id',
            'customer_alias_name',
            'sid_status',
            'debtor_in_city_administrative',
            'debtor_type_other',
            'debtor_type',
        ];
    }

    public function map($row): array
    {
        return [
            $row->customer_name,
            $row->national_id_number,
            $row->identity_type,
            $row->alternate_number,
            $row->mobile_phone,
            $row->place_of_birth,
            $row->date_of_birth ? Carbon::parse($row->date_of_birth)->format('Y-m-d') : '',
            $row->gender,
            $row->religion,
            $row->mother_maiden_name,
            $row->address,
            $row->dati2_code,
            $row->urban_village,
            $row->sub_district,
            $row->postal_code,
            $row->provinceMaster?->nama,
            $row->tax_id,
            $row->customer_alias_name,
            $row->sid_status,
            $row->debtor_in_city_administrative,
            $row->debtor_type_other,
            $row->debtor_type,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Kolom yang harus diformat sebagai teks supaya tidak diubah Excel
                $textCols = ['B', 'D', 'E', 'P', 'Q'];

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
