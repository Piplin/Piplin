<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameDeploymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deployments', function ($table) {
            $table->dropForeign('deployments_project_id_foreign');
            $table->dropForeign('deployments_user_id_foreign');
        });
        Schema::rename('deployments', 'tasks');

        Schema::table('deployment_environment', function ($table) {
            $table->dropForeign('deployment_environment_deployment_id_foreign');
            $table->dropForeign('deployment_environment_environment_id_foreign');
        });
        Schema::rename('deployment_environment', 'environment_task');

        Schema::table('deploy_steps', function ($table) {
            $table->dropForeign('deploy_steps_deployment_id_foreign');
            $table->dropForeign('deploy_steps_command_id_foreign');
        });

        Schema::rename('deploy_steps', 'task_steps');


        Schema::table('environment_task', function (Blueprint $table) {
            $table->renameColumn('deployment_id', 'task_id');
        });

        Schema::table('task_steps', function (Blueprint $table) {
            $table->renameColumn('deployment_id', 'task_id');
        });

        Schema::table('server_logs', function ($table) {
            $table->dropForeign('server_logs_deploy_step_id_foreign');
            $table->renameColumn('deploy_step_id', 'task_step_id');
            $table->foreign('task_step_id')->references('id')->on('task_steps');
        });

        // Add foreign
        Schema::table('tasks', function ($table) {
            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Add foreign
        Schema::table('environment_task', function ($table) {
            $table->foreign('environment_id')->references('id')->on('environments');
            $table->foreign('task_id')->references('id')->on('tasks');
        });

        Schema::table('task_steps', function ($table) {
            $table->foreign('task_id')->references('id')->on('tasks');
            $table->foreign('command_id')->references('id')->on('commands');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('tasks', 'deployments');
        Schema::rename('environment_task', 'deployment_environment');
        Schema::rename('task_steps', 'deploy_steps');
    }
}
