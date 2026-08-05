<?php

namespace Database\Seeders;

use App\Models\Archive;
use App\Models\BranchOffice;
use App\Models\Category;
use App\Models\User;
use App\Services\ArchiveBusinessReferenceService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(ArchiveBusinessReferenceService $referenceService): void
    {
        $this->command->info('Starting generation of demo archives...');

        $adminBranchOffice = BranchOffice::query()->where('branch_code', '01')->first();
        $user = User::query()->where('username', 'admin')->first();
        if (!$user) {
            $this->command->error('Run DatabaseSeeder first. Admin user missing.');
            return;
        }

        $branches = BranchOffice::all();
        $allCategories = Category::all();
        $categoriesToSeedDWH = ['TABUNGAN', 'KREDIT', 'BILYET DEPOSITO'];

        if ($allCategories->isEmpty() || $branches->isEmpty()) {
            $this->command->error('Run DatabaseSeeder first. Missing categories or branches.');
            return;
        }

        $count = 0;
        foreach ($allCategories as $category) {
            $categoryName = $category->category_name;
            $isDwh = in_array($categoryName, $categoriesToSeedDWH);
            $limit = $isDwh ? 200 : 30; // 200 per DWH category, 30 per normal category

            $fields = $referenceService->getFieldDefinitionsForCategory($category->id)->keyBy('reference_type');

            for ($i = 1; $i <= $limit; $i++) {
                $branch = $branches->random();
                $date = Carbon::now()->subDays(rand(0, 730)); // Over the last 2 years
                $code = 'DEMO-' . strtoupper(substr($categoryName, 0, 3)) . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

                $archive = Archive::query()->updateOrCreate(
                    ['archive_code' => $code],
                    [
                        'archive_category' => $category->id,
                        'archive_user' => $user->id,
                        'archive_name' => "Arsip {$categoryName} " . rand(1000, 9999),
                        'archive_description' => "Generated dummy data for {$categoryName}",
                        'archive_path' => "archives/demo/{$code}.txt",
                        'archive_type' => 'txt',
                        'archive_branch_office' => $branch->id,
                        'archive_date' => $date->format('Y-m-d'),
                    ]
                );

                Storage::disk('public')->put(
                    "archives/demo/{$code}.txt",
                    "Isi arsip dummy: {$code}"
                );

                if ($isDwh) {
                    $cif = 'CIF' . rand(100000, 999999);
                    $account = rand(10000000, 99999999);

                    $formState = [];
                    $references = [];

                    if ($categoryName === 'TABUNGAN') {
                        $references['cif'] = $cif;
                        $references['savings_account_no'] = "TAB-{$account}";

                        DB::connection('dwh')->table('raw_savings')->updateOrInsert(
                            ['_row_key' => "DS-{$code}"],
                            ['locationid' => $branch->branch_code, 'nocif' => $cif, 'norekening' => "TAB-{$account}", 'noalt' => '', 'status_dokumen' => 'ACTIVE']
                        );
                    } elseif ($categoryName === 'KREDIT') {
                        $references['cif'] = $cif;
                        $references['loan_account_no'] = "KRD-{$account}";

                        DB::connection('dwh')->table('raw_loans')->updateOrInsert(
                            ['_row_key' => "DL-{$code}"],
                            ['locationid' => $branch->branch_code, 'nocif' => $cif, 'id' => "KRD-{$account}", 'noalt' => '', 'status_dokumen' => 'ACTIVE']
                        );
                    } elseif ($categoryName === 'BILYET DEPOSITO') {
                        $references['cif'] = $cif;
                        $references['deposito_bilyet_no'] = "DEP-{$account}";

                        DB::connection('dwh')->table('raw_time_deposits')->updateOrInsert(
                            ['_row_key' => "DD-{$code}"],
                            ['locationid' => $branch->branch_code, 'nocif' => $cif, 'nobilyet' => "DEP-{$account}", 'status_dokumen' => 'ACTIVE']
                        );
                    }

                    foreach ($references as $referenceType => $rawValue) {
                        $field = $fields->get($referenceType);
                        if ($field) {
                            $formState[$field->id] = $rawValue;
                        }
                    }

                    $referenceService->syncForArchive($archive->fresh('branchOffice.dwhMapping'), $formState);
                }

                $count++;
            }
        }

        $this->command->info("Successfully generated {$count} dummy archives with DWH references.");
    }
}
