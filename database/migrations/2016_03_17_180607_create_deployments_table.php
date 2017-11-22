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
use Piplin\Models\Deployment;

class CreateDeploymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('committer')->nullable();
            $table->string('committer_email')->default('none@example.com');
            $table->string('commit')->nullable();
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('user_id')->nullable()->default(null);
            $table->tinyInteger('status')->default(Deployment::PENDING);
            $table->boolean('is_webhook')->default(false);
            $table->string('branch')->default('master');
            $table->text('reason')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->string('source')->nullable();
            $table->string('build_url')->nullable();
            $table->text('output')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('deployments');
    }
}
