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
use Piplin\Models\ServerLog;

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
            $table->unsignedInteger('environment_id')->default(0)->after('deploy_step_id');
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
