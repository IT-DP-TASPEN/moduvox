<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $coaReferences = [
        'coas' => ['parent_id'],
        'journal_entries' => ['coa_id'],
        'coa_movements' => ['coa_id'],
        'saving_distributions' => ['counterpart_coa_id'],
        'saving_products' => [
            'liability_coa_id',
            'interest_expense_coa_id',
            'admin_fee_revenue_coa_id',
            'tax_liability_coa_id',
            'accrued_interest_payable_coa_id',
            'interest_payable_coa_id',
            'default_cash_coa_id',
            'default_bank_coa_id',
            'penalty_revenue_coa_id',
            'aba_transit_coa_id',
        ],
        'deposit_products' => [
            'liability_coa_id',
            'interest_expense_coa_id',
            'accrued_interest_payable_coa_id',
            'tax_liability_coa_id',
            'admin_fee_revenue_coa_id',
            'interest_payable_coa_id',
            'default_cash_coa_id',
            'default_bank_coa_id',
            'kas_coa_id',
            'aba_transit_coa_id',
            'penalty_revenue_coa_id',
        ],
        'loan_products' => [
            'principal_coa_id',
            'accrued_interest_coa_id',
            'accrued_interest_receivable_coa_id',
            'interest_revenue_coa_id',
            'provision_revenue_coa_id',
            'admin_fee_revenue_coa_id',
            'insurance_revenue_coa_id',
            'notary_revenue_coa_id',
            'penalty_revenue_coa_id',
            'default_cash_coa_id',
            'default_bank_coa_id',
            'ckpn_coa_id',
            'suspense_coa_id',
            'aba_transit_coa_id',
            'flagging_revenue_coa_id',
            'stamp_duty_payable_coa_id',
        ],
        'asset_categories' => [
            'asset_coa_id',
            'accumulated_depreciation_coa_id',
            'depreciation_expense_coa_id',
            'coa_aset_id',
            'coa_akum_penyusutan_id',
            'coa_beban_penyusutan_id',
            'coa_kas_id',
        ],
        'insurance_products' => [
            'premium_revenue_coa_id',
            'premium_receivable_coa_id',
            'claim_expense_coa_id',
            'claim_receivable_coa_id',
        ],
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $duplicates = DB::table('coas')
                ->select('coa_code', DB::raw('MIN(id) as keep_id'), DB::raw('GROUP_CONCAT(id) as ids'))
                ->groupBy('coa_code')
                ->havingRaw('COUNT(*) > 1')
                ->get();
        } else {
            $duplicates = DB::table('coas')
                ->select('coa_code', DB::raw('MIN(id) as keep_id'), DB::raw('GROUP_CONCAT(id ORDER BY id) as ids'))
                ->groupBy('coa_code')
                ->havingRaw('COUNT(*) > 1')
                ->get();
        }

        if ($duplicates->isEmpty()) {
            $this->ensureCoaCodeUniqueIndex();

            return;
        }

        foreach ($duplicates as $duplicate) {
            $keepId = (int) $duplicate->keep_id;
            $duplicateIds = collect(explode(',', (string) $duplicate->ids))
                ->map(fn(string $id): int => (int) $id)
                ->filter(fn(int $id): bool => $id !== $keepId)
                ->values()
                ->all();

            if ($duplicateIds === []) {
                continue;
            }

            $this->moveReferences($duplicateIds, $keepId);
            DB::table('coas')->whereIn('id', $duplicateIds)->delete();
        }

        $this->ensureCoaCodeUniqueIndex();
    }

    public function down(): void
    {
        // Tidak bisa mengembalikan row duplikat yang sudah digabung tanpa snapshot data lama.
    }

    private function moveReferences(array $duplicateIds, int $keepId): void
    {
        foreach ($this->coaReferences as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->whereIn($column, $duplicateIds)
                    ->update([$column => $keepId]);
            }
        }
    }

    private function ensureCoaCodeUniqueIndex(): void
    {
        if (!Schema::hasTable('coas') || $this->hasCoaCodeUniqueIndex()) {
            return;
        }

        Schema::table('coas', function (Blueprint $table) {
            $table->unique('coa_code', 'coas_coa_code_unique');
        });
    }

    private function hasCoaCodeUniqueIndex(): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            return DB::table('information_schema.statistics')
                ->where('table_schema', DB::raw('DATABASE()'))
                ->where('table_name', 'coas')
                ->where('column_name', 'coa_code')
                ->where('non_unique', 0)
                ->exists();
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('coas')");

            foreach ($indexes as $index) {
                if (!($index->unique ?? false)) {
                    continue;
                }

                $columns = DB::select("PRAGMA index_info('{$index->name}')");
                if (collect($columns)->pluck('name')->contains('coa_code')) {
                    return true;
                }
            }
        }

        return false;
    }
};
