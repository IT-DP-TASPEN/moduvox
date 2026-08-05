<?php

namespace App\Console\Commands\System;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeAuditMakers extends Command
{
    protected $signature = 'system:normalize-audit-makers {--commit : Apply updates. Without this, only preview counts.}';

    protected $description = 'Normalize created_by/approved_by audit columns across application tables.';

    public function handle(): int
    {
        $commit = (bool) $this->option('commit');
        $tables = $this->targetTables();
        $totalRows = 0;

        $this->info($commit ? 'Applying audit maker normalization...' : 'Previewing audit maker normalization...');
        $this->newLine();

        foreach ($tables as $tableName) {
            $createdByOne = DB::table($tableName)->where('created_by', 1)->count();
            $createdByTwo = DB::table($tableName)->where('created_by', 2)->count();
            $createdByFive = DB::table($tableName)->where('created_by', 5)->count();

            if ($createdByOne === 0 && $createdByTwo === 0 && $createdByFive === 0) {
                continue;
            }

            $totalRows += $createdByOne + $createdByTwo + $createdByFive;
            $this->line(sprintf(
                '%s: created_by=1 -> approved_by=2 (%d), created_by=2 -> created_by=1 (%d), created_by=5 -> created_by=6 approved_by=5 (%d)',
                $tableName,
                $createdByOne,
                $createdByTwo,
                $createdByFive
            ));

            if (!$commit) {
                continue;
            }

            DB::table($tableName)
                ->where('created_by', 1)
                ->update(['approved_by' => 2]);

            DB::table($tableName)
                ->where('created_by', 5)
                ->update([
                    'created_by' => 6,
                    'approved_by' => 5,
                ]);

            DB::table($tableName)
                ->where('created_by', 2)
                ->update(['created_by' => 1]);
        }

        $this->newLine();
        $this->info(($commit ? 'Updated' : 'Would update') . " {$totalRows} row(s).");

        if (!$commit) {
            $this->warn('Run with --commit to apply the changes.');
        }

        return self::SUCCESS;
    }

    private function targetTables(): array
    {
        $excluded = [
            'cache',
            'cache_locks',
            'failed_jobs',
            'job_batches',
            'jobs',
            'migrations',
            'model_has_permissions',
            'model_has_roles',
            'password_reset_tokens',
            'personal_access_tokens',
            'role_has_permissions',
            'sessions',
        ];

        $database = DB::selectOne('select database() as db')->db;

        return collect(DB::select(
            'select table_name from information_schema.tables where table_schema = ? and table_type = ?',
            [$database, 'BASE TABLE']
        ))
            ->pluck('TABLE_NAME')
            ->reject(fn (string $table) => in_array($table, $excluded, true))
            ->filter(fn (string $table) => Schema::hasColumn($table, 'created_by') && Schema::hasColumn($table, 'approved_by'))
            ->values()
            ->all();
    }
}
