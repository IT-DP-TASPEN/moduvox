<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Position;
use App\Models\PositionAllowance;
use App\Models\PositionAllowanceMaster;
use App\Models\EmploymentDetail;
use App\Models\Division;

class MigratePositionsCommand extends Command
{
    protected $signature = 'app:migrate-positions';
    protected $description = 'Migrate legacy position data to new structured format';

    public function handle()
    {
        $this->info('Starting position migration...');

        $legacyItems = PositionAllowanceMaster::all();

        foreach ($legacyItems as $legacy) {
            // 1. Find or Create Position
            // We try to guess division if name contains certain keywords
            $divisionId = $this->guessDivision($legacy->position_name);

            $position = Position::updateOrCreate(
                ['name' => $legacy->position_name],
                ['division_id' => $divisionId]
            );

            // 2. Create Initial Allowance Version
            PositionAllowance::updateOrCreate(
                [
                    'position_id' => $position->id,
                    'effective_date' => '2020-01-01' // Base history
                ],
                ['amount' => $legacy->amount]
            );

            $this->info("Migrated Position: {$legacy->position_name}");
        }

        // 3. Link Employees to New Positions
        $employees = EmploymentDetail::all();
        foreach ($employees as $emp) {
            $matchingPosition = Position::where('name', $emp->position)->first();
            if ($matchingPosition) {
                $emp->update(['position_id' => $matchingPosition->id]);
            }
        }

        $this->info('Migration completed successfully!');
    }

    private function guessDivision($name)
    {
        $name = strtolower($name);
        if (str_contains($name, 'it ') || str_contains($name, 'network') || str_contains($name, 'development') || str_contains($name, 'programmer') || str_contains($name, 'system')) {
            return Division::where('name', 'like', '%IT%')->first()?->id;
        }
        if (str_contains($name, 'kredit') || str_contains($name, 'ao ') || str_contains($name, 'account officer')) {
            return Division::where('name', 'like', '%Kredit%')->first()?->id;
        }
        if (str_contains($name, 'sdm') || str_contains($name, 'hr') || str_contains($name, 'personalia')) {
            return Division::where('name', 'like', '%SDM%')->first()?->id;
        }
        if (str_contains($name, 'keuangan') || str_contains($name, 'akuntansi') || str_contains($name, 'pajak')) {
            return Division::where('name', 'like', '%Keuangan%')->first()?->id;
        }
        
        return null; // Unassigned
    }
}
