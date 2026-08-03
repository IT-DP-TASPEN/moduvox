<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\PermitRequest;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DemoAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('is_admin', true)->first();
        $employees = User::where('is_admin', false)->get();

        // (Truncate dihapus agar data bisa menumpuk hingga 3 bulan sesuai skenario scheduler)


        // Month to seed: Current Month dynamically
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $period = CarbonPeriod::create($startDate, $endDate);

        $faker = \Faker\Factory::create('id_ID');

        foreach ($employees as $employee) {
            foreach ($period as $date) {
                // Skip weekends

                $rand = rand(1, 100);

                if ($rand <= 5) {
                    // 5% Cuti (Leave)
                    LeaveRequest::create([
                        'user_id' => $employee->id,
                        'type' => 'Cuti Tahunan',
                        'start_date' => $date->format('Y-m-d'),
                        'end_date' => $date->format('Y-m-d'),
                        'notes' => 'Acara keluarga',
                        'status' => 'approved',
                        'approved_by' => $admin->id ?? null,
                        'approved_at' => $date->copy()->subDays(2),
                    ]);
                } elseif ($rand <= 10) {
                    // 5% Izin (Permit)
                    PermitRequest::create([
                        'user_id' => $employee->id,
                        'type' => 'Sakit',
                        'requested_at' => $date->copy()->setTime(7, rand(0, 59)),
                        'notes' => 'Demam dan pusing',
                        'status' => 'approved',
                        'approved_by' => $admin->id ?? null,
                        'approved_at' => $date->copy()->setTime(8, 0),
                    ]);
                } elseif ($rand <= 15) {
                    // 5% Lembur (Overtime) - Juga ada absensi masuk & keluar
                    $this->createNormalAttendance($employee, $date);

                    OvertimeRequest::create([
                        'user_id' => $employee->id,
                        'type' => 'Reguler',
                        'start_at' => $date->copy()->setTime(17, 30),
                        'end_at' => $date->copy()->setTime(20, 30), // 3 jam lembur
                        'break_minutes' => 0,
                        'notes' => 'Menyelesaikan laporan bulanan',
                        'status' => 'approved',
                        'approved_by' => $admin->id ?? null,
                        'approved_at' => $date->copy()->setTime(17, 0),
                    ]);
                } elseif ($rand <= 95) {
                    // 80% Normal Attendance
                    $this->createNormalAttendance($employee, $date);
                } else {
                    // 5% Absent (Alpha - no record)
                }
            }
        }
    }

    private function createNormalAttendance($employee, $date)
    {
        // Masuk
        $isLate = rand(1, 100) <= 5; // 5% chance of being late (approx 1 per month per employee)
        if ($isLate) {
            $timeIn = $date->copy()->setTime(8, rand(1, 15)); // 08:01 to 08:15
        } else {
            $timeIn = $date->copy()->setTime(7, rand(30, 59)); // 07:30 to 07:59
        }
        $statusIn = $timeIn->format('H:i') > '08:00' ? 'late' : 'on-time';

        Attendance::create([
            'user_id' => $employee->id,
            'type' => 'masuk',
            'latitude' => -6.200000 + (rand(-100, 100) / 100000),
            'longitude' => 106.816666 + (rand(-100, 100) / 100000),
            'status' => $statusIn,
            'created_at' => $timeIn,
            'updated_at' => $timeIn,
        ]);

        // Keluar
        $timeOut = $date->copy()->setTime(17, rand(0, 30)); // between 17:00 and 17:30
        
        Attendance::create([
            'user_id' => $employee->id,
            'type' => 'keluar',
            'latitude' => -6.200000 + (rand(-100, 100) / 100000),
            'longitude' => 106.816666 + (rand(-100, 100) / 100000),
            'status' => 'on-time',
            'created_at' => $timeOut,
            'updated_at' => $timeOut,
        ]);
    }
}
