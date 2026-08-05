<?php

namespace App\Imports;

use App\Models\InsuranceRate;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InsuranceRateImport implements ToCollection
{
    protected $productId;

    public function __construct($productId)
    {
        $this->productId = $productId;
    }

    public function collection(Collection $rows)
    {
        // Identify header - check if the first row is numeric (age)
        $firstRow = $rows->first();
        if ($firstRow && !is_numeric($firstRow[0])) {
            $rows->shift(); // Remove header if it's not numeric
        }

        // We use Upsert to be fast and avoid duplicate key issues
        $data = [];
        foreach ($rows as $rowIndex => $row) {
            if (!isset($row[0]) || !is_numeric($row[0])) continue;

            $age = (int)$row[0];
            if ($age <= 0) continue;

            for ($i = 1; $i < count($row); $i++) {
                $year = $i;
                $rate = $row[$i];

                if (!is_numeric($rate) && !empty($rate)) continue;
                
                $data[] = [
                    'insurance_product_id' => $this->productId,
                    'age' => $age,
                    'tenor_months' => $year * 12,
                    'rate' => (float)$rate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Chunked insert/update
                if (count($data) >= 1000) {
                    $this->upsertData($data);
                    $data = [];
                }
            }
        }

        if (!empty($data)) {
            $this->upsertData($data);
        }
    }

    protected function upsertData(array $data)
    {
        InsuranceRate::upsert(
            $data,
            ['insurance_product_id', 'age', 'tenor_months'],
            ['rate', 'updated_at']
        );
    }
}
