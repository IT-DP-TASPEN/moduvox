<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mobile_access') && ! Schema::hasColumn('mobile_access', 'activated_at')) {
            Schema::table('mobile_access', function (Blueprint $table) {
                $table->timestamp('activated_at')->nullable()->after('pin_hash');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mobile_access') && Schema::hasColumn('mobile_access', 'activated_at')) {
            Schema::table('mobile_access', function (Blueprint $table) {
                $table->dropColumn('activated_at');
            });
        }
    }
};
