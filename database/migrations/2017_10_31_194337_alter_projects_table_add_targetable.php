<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Fixhub\Models\Project;

class AlterProjectsTableAddTargetable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('targetable_id')->default(0)->after('group_id');
            $table->string('targetable_type')->default('')->after('group_id');
            $table->index(['targetable_id', 'targetable_type']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $projects = Project::withTrashed()->get();

            foreach ($projects as $project) {
                $project->targetable_id= $project->group_id;
                $project->targetable_type = 'Fixhub\\Models\\ProjectGroup';
                $project->save();
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('projects_group_id_foreign');
            $table->dropColumn('group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('targetable_id');
            $table->dropColumn('targetable_type');
            $table->unsignedInteger('group_id')->change();
            $table->foreign('group_id')->references('id')->on('groups');
        });
    }
}
