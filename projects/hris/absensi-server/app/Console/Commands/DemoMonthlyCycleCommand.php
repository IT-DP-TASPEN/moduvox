<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\PermitRequest;
use App\Models\OvertimeRequest;
use App\Models\Salary;
use App\Models\User;
use App\Services\SalaryService;
use Illuminate\Support\Facades\Artisan;

class DemoMonthlyCycleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:monthly-cycle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly dummy data (attendance & salary) and cleanup data older than 3 months.';

    /**
     * Execute the console command.
     */
    public function handle(SalaryService $salaryService)
    {
        $this->info('Starting Demo Monthly Cycle...');

        // 1. Delete data older than 3 months
        $this->info('Cleaning up old data (older than 3 months)...');
        $threeMonthsAgo = Carbon::now()->subMonths(3)->startOfMonth();
        
        Attendance::where('created_at', '<', $threeMonthsAgo)->delete();
        LeaveRequest::where('created_at', '<', $threeMonthsAgo)->delete();
        PermitRequest::where('created_at', '<', $threeMonthsAgo)->delete();
        OvertimeRequest::where('created_at', '<', $threeMonthsAgo)->delete();
        Salary::where('year', '<', $threeMonthsAgo->year)
              ->orWhere(function ($q) use ($threeMonthsAgo) {
                  $q->where('year', '=', $threeMonthsAgo->year)
                    ->where('month', '<', $threeMonthsAgo->month);
              })->delete();

        // 2. Run the DemoAttendanceSeeder to generate attendance for current month
        $this->info('Generating attendance for the current month...');
        Artisan::call('db:seed', ['--class' => 'DemoAttendanceSeeder']);

        // 3. Generate Salary for the current month
        $this->info('Generating salaries for all employees...');
        $users = User::where('is_admin', false)->get();
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        
        $count = 0;
        foreach ($users as $user) {
            try {
                $data = $salaryService->calculate($user, $month, $year);
                unset($data['overtime_hours']); // Remove UI-only field

                Salary::updateOrCreate(
                    ['user_id' => $user->id, 'month' => $month, 'year' => $year],
                    $data
                );
                $count++;
            } catch (\Exception $e) {
                // skip if missing employment data
            }
        }
        $this->info("Successfully generated salaries for {$count} employees.");
        $this->info('Monthly cycle completed successfully!');
    }
}
