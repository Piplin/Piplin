<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password', 60);
            $table->string('nickname')->nullable();
            $table->rememberToken();
            $table->tinyInteger('level')->default(2);
            $table->string('email_token')->nullable();
            $table->string('avatar')->nullable();
            $table->string('language')->nullable();
            $table->string('skin')->nullable();
            $table->string('google2fa_secret')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('name');
            $table->unique('email');
            $table->index('email_token');
        });

        User::create([
            'name'           => 'fixhub',
            'nickname'       => 'Fixhub',
            'email'          => 'fixhub@fixhub.org',
            'password'       => bcrypt('fixhub'),
            'remember_token' => str_random(10),
            'level'          => User::LEVEL_ADMIN,
            'avatar'         => '/img/noavatar.png',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
