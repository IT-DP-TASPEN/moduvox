<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'Promotion', 'Increment', 'Rotation', 'Demotion'
            $table->string('field'); // 'grade', 'skg', 'position', 'basic_salary', etc.
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->string('document_number')->nullable(); // Nomor SK
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_histories');
    }
};
