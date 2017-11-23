<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameDeployEnvTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('environment_task', function (Blueprint $table) {
            $table->renameColumn('deployment_id', 'task_id');
        });

        Schema::table('task_steps', function (Blueprint $table) {
            $table->renameColumn('deployment_id', 'task_id');
        });

        Schema::table('server_logs', function (Blueprint $table) {
            $table->renameColumn('deploy_step_id', 'task_step_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deployment_environment', function (Blueprint $table) {
            $table->renameColumn('task_id', 'deployment_id');
        });

        Schema::table('deploy_steps', function (Blueprint $table) {
            $table->renameColumn('task_id', 'deployment_id');
        });

        Schema::table('server_logs', function (Blueprint $table) {
            $table->renameColumn('task_step_id', 'deploy_step_id');
        });
    }
}
