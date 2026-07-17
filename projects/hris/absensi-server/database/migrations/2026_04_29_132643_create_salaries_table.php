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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            
            // Earnings (Pendapatan)
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('overtime_meal_pay', 15, 2)->default(0);
            $table->decimal('tax_allowance', 15, 2)->default(0);
            $table->decimal('position_allowance', 15, 2)->default(0);
            $table->decimal('performance_allowance', 15, 2)->default(0); // Individual KPI
            
            // Deductions (Potongan)
            $table->decimal('income_tax', 15, 2)->default(0);
            $table->decimal('jht_employee', 15, 2)->default(0);
            $table->decimal('jp_employee', 15, 2)->default(0);
            $table->decimal('jkn_employee', 15, 2)->default(0);
            $table->decimal('taspen_save_deduction', 15, 2)->default(0);
            
            // Company Paid (Pendapatan Non THP)
            $table->decimal('jkk_company', 15, 2)->default(0);
            $table->decimal('jkm_company', 15, 2)->default(0);
            $table->decimal('jht_company', 15, 2)->default(0);
            $table->decimal('jp_company', 15, 2)->default(0);
            $table->decimal('jkn_company', 15, 2)->default(0);
            $table->decimal('pension_premium', 15, 2)->default(0);
            $table->decimal('taspen_save_allowance', 15, 2)->default(0);

            // Totals
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->decimal('total_non_thp', 15, 2)->default(0);
            $table->decimal('total_gross', 15, 2)->default(0);

            $table->string('status')->default('draft'); // draft, published
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
