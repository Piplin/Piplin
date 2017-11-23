<?php

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
        Schema::rename('deployments', 'tasks');
        Schema::rename('deployment_environment', 'environment_task');
        Schema::rename('deploy_steps', 'task_steps');
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
