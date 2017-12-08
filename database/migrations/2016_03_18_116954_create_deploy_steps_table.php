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

class CreateDeployStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deploy_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deployment_id');
            $table->unsignedInteger('stage');
            $table->unsignedInteger('command_id')->nullable();
            $table->timestamps();

            $table->foreign('deployment_id')->references('id')->on('deployments');
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
        Schema::drop('deploy_steps');
    }
}
