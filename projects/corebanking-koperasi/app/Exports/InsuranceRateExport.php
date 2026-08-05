<?php

namespace App\Exports;

use App\Models\InsuranceRate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class InsuranceRateExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $productId;
    protected $maxYear;

    public function __construct($productId, $maxYear)
    {
        $this->productId = $productId;
        $this->maxYear = $maxYear;
    }

    public function collection()
    {
        $ages = InsuranceRate::where('insurance_product_id', $this->productId)
            ->select('age')
            ->distinct()
            ->orderBy('age', 'asc')
            ->pluck('age');

        $data = new Collection();

        foreach ($ages as $age) {
            $row = [$age];
            $rates = InsuranceRate::where('insurance_product_id', $this->productId)
                ->where('age', $age)
                ->get()
                ->keyBy(fn($i) => $i->tenor_months / 12);

            for ($y = 1; $y <= $this->maxYear; $y++) {
                $row[] = $rates[$y]->rate ?? 0;
            }
            
            $data->push($row);
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = ['Usia'];
        for ($y = 1; $y <= $this->maxYear; $y++) {
            $headings[] = "JKW {$y} THN";
        }
        return $headings;
    }
}
