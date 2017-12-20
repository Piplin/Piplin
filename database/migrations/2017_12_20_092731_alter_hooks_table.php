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

class AlterHooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hooks', function (Blueprint $table) {
            $table->renameColumn('on_deployment_success', 'on_task_success');
        });

        Schema::table('hooks', function (Blueprint $table) {
            $table->renameColumn('on_deployment_failure', 'on_task_failure');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hooks', function (Blueprint $table) {
            $table->renameColumn('on_task_success', 'on_deployment_success');
        });

        Schema::table('hooks', function (Blueprint $table) {
            $table->renameColumn('on_task_failure', 'on_deployment_failure');
        });
    }
}
