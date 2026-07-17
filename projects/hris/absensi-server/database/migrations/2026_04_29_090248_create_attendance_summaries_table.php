<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_summaries', function (Blueprint $row) {
            $row->id();
            $row->foreignId('user_id')->constrained()->onDelete('cascade');
            $row->date('date');
            $row->boolean('is_working_day')->default(true);
            $row->boolean('is_attendance')->default(false);
            $row->time('check_in')->nullable();
            $row->time('check_out')->nullable();
            $row->integer('duration_minutes')->default(0);
            $row->integer('late_minutes')->default(0);
            $row->integer('early_departure_minutes')->default(0);
            $row->string('leave_type')->nullable(); // special, sick, annual
            $row->integer('leave_days')->default(0);
            $row->integer('permit_count')->default(0);
            $row->integer('outside_duty_count')->default(0);
            $row->integer('overtime_minutes')->default(0);
            $row->string('status')->default('alpa'); // hadir, cuti, izin, alpa, tugas_luar
            $row->timestamps();

            $row->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
    }
};
