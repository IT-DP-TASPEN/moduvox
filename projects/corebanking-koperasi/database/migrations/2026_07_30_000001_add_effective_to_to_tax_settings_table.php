<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('tax_settings', 'effective_to')) {
                $table->date('effective_to')->nullable()->after('effective_from');
            }
        });

        DB::table('tax_settings')
            ->whereNull('effective_to')
            ->whereNotNull('effective_from')
            ->orderBy('id')
            ->get(['id', 'effective_from'])
            ->each(function ($setting) {
                DB::table('tax_settings')
                    ->where('id', $setting->id)
                    ->update([
                        'effective_to' => \Carbon\Carbon::parse($setting->effective_from)->endOfYear()->toDateString(),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('tax_settings', function (Blueprint $table) {
            if (Schema::hasColumn('tax_settings', 'effective_to')) {
                $table->dropColumn('effective_to');
            }
        });
    }
};
