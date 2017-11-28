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

class CreateArtifactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artifacts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file_name');
            $table->integer('file_size');
            $table->string('mime');
            $table->unsignedInteger('task_id');
            $table->unsignedInteger('server_log_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('task_id')->references('id')->on('tasks');
            $table->foreign('server_log_id')->references('id')->on('server_logs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('artifacts');
    }
}
