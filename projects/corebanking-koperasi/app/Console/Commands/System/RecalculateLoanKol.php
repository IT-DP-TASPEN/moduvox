<?php

namespace App\Console\Commands\System;

use App\Services\LoanOperationService;
use Illuminate\Console\Command;

class RecalculateLoanKol extends Command
{
    protected $signature = 'loan:recalculate-kol';

    protected $description = 'Recalculate DPD and KOL level for active/NPL loan accounts';

    public function handle(LoanOperationService $service): int
    {
        $count = $service->recalculateCollectibilityForAll();
        $this->info("Recalculated KOL for {$count} loan account(s).");

        return self::SUCCESS;
    }
}
