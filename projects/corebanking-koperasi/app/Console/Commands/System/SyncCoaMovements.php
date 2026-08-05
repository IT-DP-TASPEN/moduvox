<?php

namespace App\Console\Commands\System;

use App\Models\Journal;
use App\Services\CoaMovementService;
use Illuminate\Console\Command;

class SyncCoaMovements extends Command
{
    protected $signature = 'accounting:sync-coa-movements {--branch=} {--from-date=}';

    protected $description = 'Backfill dan sinkronisasi ulang tabel coa_movements dari jurnal APPROVED';

    public function handle(CoaMovementService $service): int
    {
        $query = Journal::query()
            ->with('entries')
            ->where('status', 'APPROVED')
            ->when($this->option('branch'), fn($q) => $q->where('branch_id', (int) $this->option('branch')))
            ->when($this->option('from-date'), fn($q) => $q->whereDate('transaction_date', '>=', $this->option('from-date')))
            ->orderBy('transaction_date')
            ->orderBy('id');

        $count = 0;
        $query->chunkById(100, function ($journals) use ($service, &$count) {
            foreach ($journals as $journal) {
                $service->syncForJournal($journal);
                $count++;
            }
        });

        $this->info("Sinkronisasi selesai. Jurnal diproses: {$count}");

        return self::SUCCESS;
    }
}
