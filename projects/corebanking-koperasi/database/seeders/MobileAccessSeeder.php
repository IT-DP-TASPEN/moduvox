<?php

namespace Database\Seeders;

use App\Models\Cif;
use App\Models\MobileAccess;
use Illuminate\Database\Seeder;

/**
 * MobileAccessSeeder
 * -------------------------------------------------
 * Membuat akun mobile demo untuk nasabah CIF yang sudah ada.
 * Jalankan: php artisan db:seed --class=MobileAccessSeeder
 *
 * Credentials demo:
 *   Username : nasabah01 / nasabah02 / nasabah03
 *   Password : Password123
 *   PIN      : 123456
 * -------------------------------------------------
 */
class MobileAccessSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil 3 CIF pertama yang ada di database
        $cifs = Cif::take(3)->get();

        if ($cifs->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada data CIF ditemukan. Jalankan seeder CIF terlebih dahulu.');
            return;
        }

        $demoAccounts = [
            ['username' => 'nasabah01', 'password' => 'Password123', 'pin' => '123456'],
            ['username' => 'nasabah02', 'password' => 'Password123', 'pin' => '654321'],
            ['username' => 'nasabah03', 'password' => 'Password123', 'pin' => '111111'],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($cifs as $index => $cif) {
            // Cek apakah CIF sudah punya akun mobile
            if (MobileAccess::where('cif_id', $cif->id)->exists()) {
                $this->command->line("  ⤷ Dilewati: {$cif->name} ({$cif->cif_no}) — sudah punya akun mobile.");
                $skipped++;
                continue;
            }

            $demo = $demoAccounts[$index] ?? $demoAccounts[0];
            // Pastikan username unik jika ada bentrok
            $username = $demo['username'];
            $counter  = 1;
            while (MobileAccess::where('username', $username)->exists()) {
                $username = $demo['username'] . '_' . $counter++;
            }

            $mobile = new MobileAccess([
                'cif_id'          => $cif->id,
                'cif_no'          => $cif->cif_no,
                'username'        => $username,
                'is_active'       => true,
                'wrong_pin_count' => 0,
                'created_by'      => 1,
                'updated_by'      => 1,
            ]);

            $mobile->setPassword($demo['password']);
            $mobile->setPin($demo['pin']);
            $mobile->save();

            $this->command->info("  ✓ Akun mobile dibuat: [{$cif->cif_no}] {$cif->name} → username: {$username}");
            $created++;
        }

        $this->command->newLine();
        $this->command->line("─────────────────────────────────────────────────");
        $this->command->info("  Mobile Access Seeder selesai.");
        $this->command->line("  ✅ Dibuat  : {$created} akun");
        $this->command->line("  ⏭️  Dilewati: {$skipped} akun (sudah ada)");
        $this->command->line("  🔑 Password: Password123");
        $this->command->line("  🔢 PIN     : 123456 / 654321 / 111111");
        $this->command->line("─────────────────────────────────────────────────");
        $this->command->newLine();
        $this->command->warn("  ⚠️  Credentials di atas hanya untuk TESTING.");
        $this->command->warn("  ⚠️  Minta nasabah mengganti password & PIN setelah login pertama.");
    }
}
