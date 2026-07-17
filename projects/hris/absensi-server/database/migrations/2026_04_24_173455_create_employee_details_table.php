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
        // Tabel Mutasi
        Schema::create('mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('Mutasi'); // Mutasi, Promosi, Demosi
            $table->string('old_position')->nullable();
            $table->string('new_position')->nullable();
            $table->foreignId('old_office_id')->nullable()->constrained('offices');
            $table->foreignId('new_office_id')->nullable()->constrained('offices');
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Tabel SP (Surat Peringatan)
        Schema::create('warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('level'); // SP 1, SP 2, SP 3
            $table->string('reason');
            $table->date('date');
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });

        // Tabel File Karyawan
        Schema::create('user_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // KTP, Ijazah, dll
            $table->string('path');
            $table->string('file_type')->nullable(); // pdf, jpg, dll
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_details');
    }
};
