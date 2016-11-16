<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIdentitiesTable extends Migration
{
    public function up()
    {
        // Create table for storing Social sign-in providers
        Schema::create('providers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create table for associating providers to identities (Many-to-Many)
        Schema::create('identities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('extern_uid');
            $table->integer('provider_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('nickname')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('provider_id')->references('id')->on('providers')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->index('extern_uid');
            $table->index('provider_id');
            $table->unique(['user_id', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('identities');
        Schema::drop('providers');
    }
}
