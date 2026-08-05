<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->applicationTables() as $tableName) {
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
        // No-op: this migration only fills audit-column gaps and must not remove
        // audit columns that existed before it ran.
    }

    private function applicationTables(): array
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

        return collect(Schema::getConnection()->getSchemaBuilder()->getTableListing())
            ->reject(fn (string $table) => str_contains($table, '.'))
            ->reject(fn (string $table) => in_array($table, $excluded, true))
            ->values()
            ->all();
    }
};
