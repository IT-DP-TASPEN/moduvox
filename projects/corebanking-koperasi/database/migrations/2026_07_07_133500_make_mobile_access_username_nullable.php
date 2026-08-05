<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mobile_access')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE mobile_access MODIFY username VARCHAR(50) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('mobile_access')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE mobile_access SET username = CONCAT('pending_', id) WHERE username IS NULL");
            DB::statement('ALTER TABLE mobile_access MODIFY username VARCHAR(50) NOT NULL');
        }
    }
};
