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
        if (app()->environment(['local', 'testing'])) {
            Schema::create('raw_savings', function (Blueprint $table) {
                $table->string('_row_key')->primary();
                $table->string('locationid')->nullable();
                $table->string('nocif')->nullable();
                $table->string('norekening')->nullable();
                $table->string('noalt')->nullable();
                $table->string('status_dokumen')->nullable();
                $table->timestamps();
            });

            Schema::create('raw_loans', function (Blueprint $table) {
                $table->string('_row_key')->primary();
                $table->string('locationid')->nullable();
                $table->string('nocif')->nullable();
                $table->string('id')->nullable(); // loan account no
                $table->string('noalt')->nullable();
                $table->string('status_dokumen')->nullable();
                $table->timestamps();
            });

            Schema::create('raw_time_deposits', function (Blueprint $table) {
                $table->string('_row_key')->primary();
                $table->string('locationid')->nullable();
                $table->string('nocif')->nullable();
                $table->string('nobilyet')->nullable();
                $table->string('status_dokumen')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->environment(['local', 'testing'])) {
            Schema::dropIfExists('raw_savings');
            Schema::dropIfExists('raw_loans');
            Schema::dropIfExists('raw_time_deposits');
        }
    }
};
