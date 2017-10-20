<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Server;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('ip_address');
            $table->string('user');
            $table->string('path');
            $table->integer('port')->default(22);
            $table->tinyInteger('status')->default(Server::UNTESTED);
            $table->unsignedInteger('order')->default(0);
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('environment_id');
            $table->longtext('output')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('environment_id')->references('id')->on('environments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('servers');
    }
}
