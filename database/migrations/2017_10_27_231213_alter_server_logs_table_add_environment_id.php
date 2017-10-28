<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Fixhub\Models\ServerLog;

class AlterServerLogsTableAddEnvironmentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('server_logs', function (Blueprint $table) {
            $table->unsignedInteger('environment_id')->after('deploy_step_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('server_logs', function (Blueprint $table) {
            $table->dropColumn('environment_id');
        });
    }
}
