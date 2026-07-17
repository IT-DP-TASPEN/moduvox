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
        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Kepegawaian
            $table->date('join_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('company_account_number')->nullable();
            $table->string('grade')->nullable();
            $table->string('skg')->nullable();
            $table->string('dplk_bni_account_number')->nullable();

            // BPJS
            $table->string('bpjs_ketenagakerjaan_no')->nullable();
            $table->string('bpjs_kesehatan_no')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employment_details');
    }
};
