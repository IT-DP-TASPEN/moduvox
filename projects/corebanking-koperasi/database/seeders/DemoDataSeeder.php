<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Cif;
use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use App\Models\LoanAccount;
use App\Models\DepositAccount;
use App\Models\LoanTransaction;
use App\Models\DepositTransaction;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Coa;
use App\Models\TaxSetting;
use App\Models\Rekanan;
use App\Models\Asset;
use App\Models\MasterShu;
use App\Models\LoanSchedule;
use App\Models\DepositSchedule;
use App\Models\Branch;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $branch = Branch::first();
        
        if (!$branch) {
            $this->command->error('Run DatabaseSeeder first to ensure a Branch exists.');
            return;
        }

        $this->command->info('Mulai generate data dummy...');

        $subdistrict = \Illuminate\Support\Facades\DB::table('subdistricts')->first();
        if ($subdistrict) {
            $district = \Illuminate\Support\Facades\DB::table('districts')->where('id', $subdistrict->district_id)->first();
            $city = \Illuminate\Support\Facades\DB::table('cities')->where('id', $district->regency_id)->first();
            $province = \Illuminate\Support\Facades\DB::table('provinces')->where('id', $city->province_id)->first();
            
            $subdistrict_id = $subdistrict->id;
            $district_id = $district->id;
            $city_id = $city->id;
            $province_id = $province->id;
        } else {
            $district_id = $city_id = $province_id = $subdistrict_id = 1;
        }

        // 1. Generate 50 CIFs (Anggota)
        $cifs = [];
        for ($i = 0; $i < 50; $i++) {
            $cif = Cif::create([
                'cif_no' => '100' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'branch_id' => $branch->id,
                'nik' => $faker->nik(),
                'npwp' => $faker->numerify('##.###.###.#-###.###'),
                'name' => $faker->name(),
                'birth_place' => $faker->city(),
                'birth_date' => $faker->date('Y-m-d', '-20 years'),
                'gender' => $faker->randomElement(['MALE', 'FEMALE']),
                'blood_type' => $faker->randomElement(['A', 'B', 'AB', 'O']),
                'mother_maiden_name' => $faker->name('female'),
                'marital_status' => $faker->randomElement(['SINGLE', 'MARRIED', 'WIDOWED', 'DIVORCED']),
                'religion' => $faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
                'occupation' => $faker->jobTitle(),
                'company_name' => $faker->company(),
                'income_range' => $faker->randomElement(['< 5 Juta', '5 - 10 Juta', '> 10 Juta']),
                'address' => $faker->streetAddress(),
                'rt' => $faker->numerify('0#'),
                'rw' => $faker->numerify('0#'),
                'province_id' => $province_id,
                'city_id' => $city_id,
                'district_id' => $district_id,
                'subdistrict_id' => $subdistrict_id,
                'postal_code' => '40135',
                'phone' => $faker->phoneNumber(),
                'email' => $faker->safeEmail(),
                'status' => 'ACTIVE',
            ]);
            $cifs[] = $cif;
        }

        // 2. Generate Savings Accounts
        $savingProducts = \App\Models\SavingProduct::all();
        if ($savingProducts->isEmpty()) {
            $this->command->info('Tidak ada SavingProduct, skip data tabungan.');
        } else {
            foreach ($cifs as $index => $cif) {
                if ($index % 5 !== 0) {
                    $product = $savingProducts->random();
                    $account = SavingAccount::create([
                        'branch_id' => $branch->id,
                        'cif_id' => $cif->id,
                        'saving_product_id' => $product->id,
                        'account_no' => '200' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                        'status' => 'ACTIVE',
                        'balance' => $faker->numberBetween(500000, 25000000),
                        'blocked_balance' => 0,
                        'opened_at' => $faker->dateTimeBetween('-2 years', '-1 month'),
                        'created_by' => 1,
                    ]);

                    for ($t = 0; $t < rand(3, 10); $t++) {
                        $amt = $faker->numberBetween(100000, 5000000);
                        SavingTransaction::create([
                            'saving_account_id' => $account->id,
                            'transaction_no' => 'ST' . uniqid(),
                            'transaction_date' => $faker->date(),
                            'type' => 'DEPOSIT',
                            'channel' => 'CASH',
                            'amount' => $amt,
                            'balance_after' => $account->balance + $amt,
                            'reference_no' => 'REF' . uniqid(),
                            'description' => 'Setoran Tunai Demo',
                            'created_by' => 1,
                        ]);
                    }
                }
            }
        }

        // 3. Generate Loan Accounts
        $loanProducts = \App\Models\LoanProduct::all();
        if ($loanProducts->isEmpty()) {
            $this->command->info('Tidak ada LoanProduct, skip data pinjaman.');
        } else {
            $loanCifs = array_slice($cifs, 0, 15);
            foreach ($loanCifs as $index => $cif) {
                $product = $loanProducts->random();
                $plafond = $faker->numberBetween(5, 50) * 1000000;
                $loanAccount = LoanAccount::create([
                    'branch_id' => $branch->id,
                    'cif_id' => $cif->id,
                    'loan_product_id' => $product->id,
                    'account_no' => '300' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                    'status' => 'ACTIVE',
                    'principal_amount' => $plafond,
                    'outstanding_principal' => $plafond * 0.8,
                    'outstanding_interest' => ($plafond * 0.1) * 0.8,
                    'tenor' => $faker->randomElement([12, 24, 36, 48]),
                    'interest_rate' => $product->interest_rate_max ?? 12,
                    'calculation_method' => 'FLAT',
                    'disbursement_date' => $faker->dateTimeBetween('-1 year', '-1 month')->format('Y-m-d'),
                    'created_by' => 1,
                ]);

                // Generate Loan Schedules
                $pokok = $plafond / $loanAccount->tenor;
                $bunga = ($plafond * ($loanAccount->interest_rate / 100)) / 12;
                for ($j = 1; $j <= $loanAccount->tenor; $j++) {
                    LoanSchedule::create([
                        'loan_account_id' => $loanAccount->id,
                        'installment_number' => $j,
                        'due_date' => Carbon::parse($loanAccount->disbursement_date)->addMonths($j)->format('Y-m-d'),
                        'principal_amount' => $pokok,
                        'interest_amount' => $bunga,
                        'penalty_amount' => 0,
                        'principal_paid' => 0,
                        'interest_paid' => 0,
                        'penalty_paid' => 0,
                        'status' => 'UNPAID',
                        'created_by' => 1,
                    ]);
                }
            }
        }

        // 4. Generate Deposit Accounts
        $depositProducts = \App\Models\DepositProduct::all();
        if ($depositProducts->isEmpty()) {
            $this->command->info('Tidak ada DepositProduct, skip data deposito.');
        } else {
            $depositCifs = array_slice($cifs, 15, 10);
            foreach ($depositCifs as $index => $cif) {
                $product = $depositProducts->random();
                $depositAccount = DepositAccount::create([
                    'branch_id' => $branch->id,
                    'cif_id' => $cif->id,
                    'deposit_product_id' => $product->id,
                    'account_no' => '400' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                    'status' => 'ACTIVE',
                    'amount' => $faker->numberBetween(10, 100) * 1000000,
                    'tenor' => $faker->randomElement([1, 3, 6, 12]),
                    'interest_rate' => $product->max_interest_rate ?? 6,
                    'rollover_type' => $faker->randomElement(['NONE', 'PRINCIPAL', 'PRINCIPAL_INTEREST']),
                    'fund_channel' => 'KAS',
                    'placement_date' => $faker->dateTimeBetween('-6 months', '-1 month')->format('Y-m-d'),
                    'maturity_date' => $faker->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
                    'created_by' => 1,
                ]);

                // Generate Deposit Schedules
                $bungaBulan = ($depositAccount->amount * ($depositAccount->interest_rate / 100)) / 12;
                for ($j = 1; $j <= $depositAccount->tenor; $j++) {
                    DepositSchedule::create([
                        'deposit_account_id' => $depositAccount->id,
                        'month_index' => $j,
                        'schedule_date' => Carbon::parse($depositAccount->placement_date)->addMonths($j)->format('Y-m-d'),
                        'gross_interest' => $bungaBulan,
                        'tax_amount' => $bungaBulan * 0.2,
                        'net_interest' => $bungaBulan * 0.8,
                        'status' => 'PENDING',
                    ]);
                }
            }
        }

        // 5. Generate Loan Transactions
        $this->command->info('Mulai generate transaksi pinjaman...');
        $loanAccounts = LoanAccount::all();
        foreach ($loanAccounts as $loan) {
            for ($t = 0; $t < rand(2, 5); $t++) {
                $principal = $loan->principal_amount / $loan->tenor;
                $interest = ($loan->principal_amount * ($loan->interest_rate / 100)) / 12;
                LoanTransaction::create([
                    'loan_account_id' => $loan->id,
                    'reference_number' => 'LREF' . uniqid(),
                    'transaction_type' => 'REPAYMENT_MANUAL',
                    'channel' => 'CASH',
                    'amount_principal' => $principal,
                    'amount_interest' => $interest,
                    'amount_penalty' => 0,
                    'amount_admin_fee' => 0,
                    'amount_provision' => 0,
                    'amount_insurance_fee' => 0,
                    'total_amount' => $principal + $interest,
                    'description' => 'Pembayaran Angsuran Pinjaman',
                    'created_by' => 1,
                ]);
            }
        }

        // 6. Generate Deposit Transactions
        $this->command->info('Mulai generate transaksi deposito...');
        $depositAccounts = DepositAccount::all();
        foreach ($depositAccounts as $deposit) {
            DepositTransaction::create([
                'deposit_account_id' => $deposit->id,
                'transaction_no' => 'DT' . uniqid(),
                'transaction_date' => $deposit->placement_date,
                'type' => 'PLACEMENT',
                'channel' => 'CASH',
                'amount' => $deposit->amount,
                'reference_no' => 'REF' . uniqid(),
                'description' => 'Penempatan Deposito',
                'created_by' => 1,
            ]);
        }

        // 7. Generate Dummy Journals
        $this->command->info('Mulai generate data jurnal umum...');
        $coas = Coa::where('is_leaf', true)->inRandomOrder()->take(10)->get();
        if ($coas->count() >= 2) {
            for ($j = 0; $j < 15; $j++) {
                $journal = Journal::create([
                    'branch_id' => $branch->id,
                    'transaction_date' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
                    'reference_no' => 'JRN' . uniqid(),
                    'description' => 'Jurnal Transaksi Operasional ' . rand(100, 999),
                    'journal_type' => Journal::TYPE_MANUAL,
                    'status' => 'APPROVED',
                    'created_by' => 1,
                    'approved_by' => 1,
                    'approved_at' => now(),
                ]);

                $amount = $faker->numberBetween(1, 100) * 100000;
                
                // Debit
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $coas->random()->id,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Debit ' . $journal->reference_no,
                ]);

                // Credit
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $coas->random()->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Kredit ' . $journal->reference_no,
                ]);
            }
        }

        // 8. Generate Tax Settings
        $this->command->info('Mulai generate pengaturan pajak...');
        $expenseCoa = Coa::where('is_leaf', true)->inRandomOrder()->first();
        $payableCoa = Coa::where('is_leaf', true)->inRandomOrder()->first();
        if ($expenseCoa && $payableCoa) {
            TaxSetting::create([
                'name' => 'Pajak Bunga Simpanan (PPh Pasal 4 Ayat 2)',
                'tax_rate' => 20.00,
                'calculation_base' => 'TOTAL_REVENUE',
                'expense_coa_id' => $expenseCoa->id,
                'payable_coa_id' => $payableCoa->id,
                'effective_from' => '2023-01-01',
                'is_active' => true,
                'created_by' => 1,
            ]);
        }

        // 9. Generate Rekanan (Vendors)
        $this->command->info('Mulai generate data rekanan...');
        for ($i = 1; $i <= 3; $i++) {
            Rekanan::create([
                'rekanan_code' => 'VEN-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => $faker->company(),
                'contact_person' => $faker->name(),
                'phone' => $faker->phoneNumber(),
                'email' => $faker->companyEmail(),
                'address' => $faker->address(),
                'npwp' => $faker->numerify('##.###.###.#-###.###'),
                'bank_name' => 'Bank Mandiri',
                'bank_account_no' => $faker->numerify('##########'),
                'bank_account_name' => $faker->company(),
                'is_active' => true,
                'created_by' => 1,
            ]);
        }

        // 10. Generate Assets
        $this->command->info('Mulai generate data aset...');
        $assetCategory = \App\Models\AssetCategory::first();
        if ($assetCategory) {
            for ($i = 1; $i <= 5; $i++) {
                $price = $faker->numberBetween(5, 50) * 1000000;
                Asset::create([
                    'asset_code' => 'AST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'name' => 'Aset Kantor ' . $i,
                    'asset_category_id' => $assetCategory->id,
                    'branch_id' => $branch->id,
                    'purchase_date' => $faker->dateTimeBetween('-3 years', '-1 month')->format('Y-m-d'),
                    'purchase_price' => $price,
                    'current_value' => $price * 0.8,
                    'accumulated_depreciation' => 0,
                    'salvage_value' => $price * 0.1,
                    'useful_life_years' => 4,
                    'useful_life_months' => 48,
                    'depreciation_method' => 'STRAIGHT_LINE',
                    'depreciation_rate' => 25,
                    'depreciation_nominal' => ($price - ($price * 0.1)) / 48,
                    'current_book_value' => $price,
                    'location' => 'Kantor Pusat',
                    'vendor' => 'Vendor A',
                    'condition' => 'GOOD',
                    'status' => 'ACTIVE',
                    'created_by' => 1,
                ]);
            }
        }

        // 11. Generate Master SHU
        $this->command->info('Mulai generate data master SHU...');
        $shuCifs = array_slice($cifs, 0, 10);
        foreach ($shuCifs as $cif) {
            $savingAccount = SavingAccount::where('cif_id', $cif->id)->first();
            if ($savingAccount) {
                MasterShu::create([
                    'cif_id' => $cif->id,
                    'kriteria' => 'ANGGOTA_AKTIF',
                    'saving_account_id' => $savingAccount->id,
                ]);
            }
        }

        $this->command->info('Data dummy berhasil digenerate.');
    }
}
