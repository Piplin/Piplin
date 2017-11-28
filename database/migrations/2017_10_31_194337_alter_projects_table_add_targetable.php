<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Piplin\Models\Project;

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
                $project->targetable_id = $project->group_id;
                $project->targetable_type = 'Piplin\\Models\\ProjectGroup';
                $project->save();
            }
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
        });
    }
}
