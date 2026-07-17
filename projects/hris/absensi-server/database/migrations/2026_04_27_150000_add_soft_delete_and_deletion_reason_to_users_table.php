<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('users', 'deletion_reason')) {
                $table->string('deletion_reason')->nullable()->after('employment_status');
            }
            if (!Schema::hasColumn('users', 'deletion_note')) {
                $table->text('deletion_note')->nullable()->after('deletion_reason');
            }
            if (!Schema::hasColumn('users', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->after('deletion_note')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deleted_by')) {
                $table->dropConstrainedForeignId('deleted_by');
            }
            if (Schema::hasColumn('users', 'deletion_note')) {
                $table->dropColumn('deletion_note');
            }
            if (Schema::hasColumn('users', 'deletion_reason')) {
                $table->dropColumn('deletion_reason');
            }
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};

