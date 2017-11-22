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

class CreateSharedFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shared_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('file');
            $table->integer('targetable_id');
            $table->string('targetable_type');
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
        Schema::drop('shared_files');
    }
}
