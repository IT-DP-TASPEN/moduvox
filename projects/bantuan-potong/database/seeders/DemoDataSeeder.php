<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BanpotMaster;
use App\Models\SavingAccount;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Model;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->command->info('Starting generation of demo data for Bantuan Potong...');
        
        $faker = Faker::create('id_ID');
        
        $users = User::whereNotNull('mitra_master_id')->get();
        if ($users->isEmpty()) {
            $users = User::all();
        }

        $statuses = ['request', 'approved_mitra', 'rejected_mitra', 'on_process', 'success', 'failed', 'complete'];
        
        // Seed Saving Accounts
        for ($i = 1; $i <= 50; $i++) {
            $user = $users->random();
            SavingAccount::create([
                'wilayah' => $faker->city,
                'notas' => 'NOTAS' . $faker->unique()->numerify('######'),
                'customer_name' => $faker->name,
                'national_id_number' => $faker->numerify('327#############'),
                'identity_type' => 'KTP',
                'mobile_phone' => $faker->phoneNumber,
                'place_of_birth' => $faker->city,
                'date_of_birth' => $faker->date('Y-m-d', '-20 years'),
                'gender' => $faker->randomElement(['M', 'F']),
                'religion' => $faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
                'mother_maiden_name' => $faker->firstNameFemale,
                'address' => $faker->address,
                'dati2_code' => $faker->numerify('##'),
                'dati2_name' => $faker->city,
                'urban_village' => $faker->streetName,
                'sub_district' => $faker->citySuffix,
                'postal_code' => $faker->postcode,
                'province' => $faker->state,
                'status' => $faker->randomElement($statuses),
                'created_by' => $user->id,
                'created_mitra' => 'Mitra Demo',
            ]);
        }
        
        // Seed Banpot Masters
        for ($i = 1; $i <= 50; $i++) {
            $user = $users->random();
            $nominal = $faker->randomElement([500000, 1000000, 1500000, 2000000]);
            
            BanpotMaster::create([
                'rek_tabungan' => $faker->numerify('##########'),
                'nama_nasabah' => $faker->name,
                'notas' => 'NOTAS' . $faker->unique()->numerify('######'),
                'rek_kredit' => $faker->numerify('##########'),
                'tenor' => (string)$faker->numberBetween(12, 60),
                'angsuran_ke' => (string)$faker->numberBetween(1, 12),
                'tmt_kredit' => Carbon::now()->subMonths(6)->toDateString(),
                'tat_kredit' => Carbon::now()->addMonths(24)->toDateString(),
                'gaji_pensiun' => $nominal * 4,
                'nominal_potongan' => $nominal,
                'bank_transfer' => $faker->randomElement(['Bank Mandiri', 'BCA', 'BNI', 'BRI']),
                'rek_transfer' => $faker->numerify('##########'),
                'saldo_mengendap' => 100000,
                'jumlah_tertagih' => $nominal,
                'gaji_mengendap' => $nominal * 4,
                'sisa_gaji' => ($nominal * 4) - $nominal,
                'fee_banpot' => 10000,
                'bulan_dapem' => Carbon::now()->format('Ym01'),
                'jenis_pinbuk' => '1',
                'status_banpot' => $faker->randomElement($statuses),
                'created_by' => $user->id,
                'created_mitra' => 'Mitra Demo',
            ]);
        }
        
        $this->command->info('Demo data generated successfully.');
        Model::reguard();
    }
}
