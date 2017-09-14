<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('repository');
            $table->string('hash');
            $table->string('branch')->default('master');
            $table->text('private_key');
            $table->text('public_key');
            $table->unsignedInteger('group_id');
            $table->unsignedInteger('builds_to_keep')->default(10);
            $table->string('url')->nullable();
            $table->string('build_url')->nullable();
            $table->boolean('allow_other_branch')->default(true);
            $table->boolean('include_dev')->default(true);
            $table->tinyInteger('status')->default(Project::NOT_DEPLOYED);
            $table->dateTime('last_run')->nullable()->default(null);
            $table->dateTime('last_mirrored')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('group_id')->references('id')->on('project_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('projects');
    }
}
