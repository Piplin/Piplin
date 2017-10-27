<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Fixhub\Models\Project;
use Fixhub\Models\User;

class AlterProjectsTableAddOwnerId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedInteger('owner_id')->after('name');
        });

        $admin = User::where('level', User::LEVEL_ADMIN)->first();

        $projects = Project::withTrashed()->get();
        foreach ($projects as $project) {
            $project->owner_id = $admin->id;
            $project->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('owner_id');
        });
    }
}
