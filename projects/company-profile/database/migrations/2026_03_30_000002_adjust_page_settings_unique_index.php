<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjustPageSettingsUniqueIndex extends Migration
{
    public function up()
    {
        Schema::table('page_settings', function (Blueprint $table) {
            // Previously: unique('key')
            $table->dropUnique('page_settings_key_unique');
            $table->unique(['page', 'key']);
        });
    }

    public function down()
    {
        Schema::table('page_settings', function (Blueprint $table) {
            $table->dropUnique('page_settings_page_key_unique');
            $table->unique('key');
        });
    }
}

