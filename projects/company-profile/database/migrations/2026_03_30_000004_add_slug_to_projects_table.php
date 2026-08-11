<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddSlugToProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        $existing = DB::table('projects')->select(['id', 'title'])->orderBy('id')->get();
        $used = [];

        foreach ($existing as $row) {
            $base = Str::slug((string) $row->title);
            if ($base === '') {
                $base = 'project';
            }

            $slug = $base;
            $i = 2;
            while (in_array($slug, $used, true) || DB::table('projects')->where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }

            $used[] = $slug;

            DB::table('projects')->where('id', $row->id)->update(['slug' => $slug]);
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->unique('slug');
        });

        DB::statement('ALTER TABLE `projects` MODIFY `slug` VARCHAR(255) NOT NULL');
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
}
