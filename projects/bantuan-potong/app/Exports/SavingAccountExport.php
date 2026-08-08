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

class SavingAccountExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $records;
    protected $rowNumber = 0; // counter untuk nomor urut

    protected $religionOptions = [
        '1' => 'ISLAM',
        '2' => 'KATOLIK',
        '3' => 'KRISTEN',
        '4' => 'BUDDHA',
        '5' => 'HINDU',
        '9' => 'LAINNYA',
    ];

    protected $eduOptions = [
        '00' => 'TANPA GELAR',
        '01' => 'DIPLOMA 1',
        '02' => 'DIPLOMA 2',
        '03' => 'DIPLOMA 3',
        '04' => 'STRATA 1',
        '05' => 'STRATA 2',
        '06' => 'STRATA 3',
        '99' => 'LAINNYA',
    ];

    protected $hubAhliWarisOptions = [
        '01' => 'SUAMI/ISTRI',
        '02' => 'BAPAK/IBU KANDUNG',
        '03' => 'BAPAK/IBU MERTUA',
        '04' => 'BAPAK/IBU TIRI',
        '05' => 'BAPAK/IBU ANGKAT',
        '06' => 'KAKEK/NENEK',
        '07' => 'PAMAN/BIBI',
        '08' => 'SAUDARA KANDUNG',
        '09' => 'SAUDARA IPAR',
        '10' => 'SAUDARA TIRI',
        '11' => 'SAUDARA ANGKAT',
        '12' => 'SEPUPU KANDUNG',
        '13' => 'SEPUPU IPAR',
        '14' => 'ANAK KANDUNG',
        '15' => 'ANAK TIRI',
        '16' => 'ANAK ANGKAT',
        '17' => 'KEPONAKAN KANDUNG',
        '18' => 'KEPONAKAN IPAR',
        '19' => 'CUCU',
        '20' => 'KERABAT LAINNYA',
        '99' => 'BUKAN KERABAT',
    ];

    protected $genderOptions = [
        '1' => 'LAKI - LAKI',
        '2' => 'PEREMPUAN',
    ];

    protected $maritalStatusOptions = [
        '1' => 'BELUM MENIKAH',
        '2' => 'MENIKAH',
        '3' => 'CERAI',
        '4' => 'CERAI MATI',
    ];

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
            'No Taspen',
            'Nama Nasabah',
            'NIK',
            'Jenis Identitas',
            'No HP',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Agama',
            'Nama Ibu Kandung',
            'Alamat',
            'Provinsi',
            'Kota/Kabupaten',
            'Kode Dati 2',
            'Kelurahan',
            'Kecamatan',
            'Kode Pos',
            'NPWP',
            'Alias Nasabah',
            'Status Pernikahan',
            'Pendidikan Terakhir',
            'Nama Pasangan',
            'NIK Pasangan',
            'Kontak Darurat',
            'Nama Ahli Waris',
            'Hubungan Ahli Waris',
            'Form Buka Tabungan',
            'Status',
            'Keterangan',
            'Rekening Tabungan',
            'Created Mitra',
            'Created By',
            'Created At',
            'Updated At',
            'Wilayah',
        ];
    }


    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->notas,
            $row->customer_name,
            $row->national_id_number,
            $row->identity_type,
            $row->mobile_phone,
            $row->place_of_birth,
            $row->date_of_birth
                ? Carbon::parse($row->date_of_birth)->format('Y-m-d')
                : '',

            // ENUM / OPTIONS
            $this->genderOptions[$row->gender] ?? $row->gender,
            $this->religionOptions[$row->religion] ?? $row->religion,

            $row->mother_maiden_name,
            $row->address,

            // =====================
            // WILAYAH (FIX & RAPI)
            // =====================
            $row->provinceMaster?->nama, // Provinsi
            $row->dati2?->nama ?? '',             // Kota/Kabupaten
            $row->dati2?->dati2 ?? '',             // Kode Dati 2
            $row->urban_village,                        // Kelurahan
            $row->sub_district,                         // Kecamatan
            $row->postal_code,                          // Kode Pos

            // =====================
            // DATA LAIN
            // =====================
            $row->tax_id,
            $row->customer_alias_name,
            $this->maritalStatusOptions[$row->marital_status] ?? $row->marital_status,
            $this->eduOptions[$row->last_edu] ?? $row->last_edu,
            $row->nama_pasangan,
            $row->nik_pasangan,
            $row->kontak_darurat,
            $row->nama_ahli_waris,
            $this->hubAhliWarisOptions[$row->hub_ahli_waris] ?? $row->hub_ahli_waris,
            $row->form_buka_tab,
            ucfirst(str_replace('_', ' ', $row->status)),
            $row->keterangan,
            $row->rek_tabungan,
            $row->created_mitra,
            $row->creator->name ?? '',
            $row->created_at?->format('Y-m-d H:i:s'),
            $row->updated_at?->format('Y-m-d H:i:s'),
            $row->wilayah,
        ];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Kolom yang wajib diformat sebagai teks agar tidak auto formula
                $textCols = ['B', 'D', 'F', 'R', 'V', 'W', 'X', 'Y', 'AD', 'AE', 'AF', 'AG'];

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