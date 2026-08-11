<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach ($this->targetTables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }

                if (!Schema::hasColumn($tableName, 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable();
                }

                if (!Schema::hasColumn($tableName, 'approved_at')) {
                    $table->timestamp('approved_at')->nullable();
                }

                if (!Schema::hasColumn($tableName, 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (!Schema::hasColumn($tableName, 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }

                if (!Schema::hasColumn($tableName, 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // No-op: this only fills audit-column gaps.
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
            ->pluck('table_name')
            ->reject(fn (string $table) => in_array($table, $excluded, true))
            ->values()
            ->all();
    }
};
