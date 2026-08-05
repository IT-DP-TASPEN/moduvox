<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('saving_transactions')) {
            DB::statement("ALTER TABLE saving_transactions MODIFY channel ENUM('CASH','ABA','INTERNAL','COA') NOT NULL DEFAULT 'CASH'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('saving_transactions')) {
            DB::statement("UPDATE saving_transactions SET channel = 'CASH' WHERE channel = 'COA'");
            DB::statement("ALTER TABLE saving_transactions MODIFY channel ENUM('CASH','ABA','INTERNAL') NOT NULL DEFAULT 'CASH'");
        }
    }
};
