<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('coas')->where('coa_code', '149999')->exists()) {
            return;
        }

        $parentId = DB::table('coas')->where('coa_code', '149000')->value('id');

        DB::table('coas')->insert([
            'coa_code' => '149999',
            'name' => 'REKENING ANTARA MIGRASI / SALDO AWAL',
            'type' => 'ASSET',
            'parent_id' => $parentId,
            'is_leaf' => true,
            'is_cash' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('coas')
            ->where('coa_code', '149999')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('journal_entries')
                    ->whereColumn('journal_entries.coa_id', 'coas.id');
            })
            ->delete();
    }
};
