<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Command;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('user')->nullable()->default(null);
            $table->text('script');
            $table->integer('targetable_id');
            $table->string('targetable_type');
            $table->tinyInteger('step')->default(Command::AFTER_INSTALL);
            $table->boolean('optional')->default(false);
            $table->unsignedInteger('order')->default('0');
            $table->boolean('default_on')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['targetable_id', 'targetable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('commands');
    }
}
