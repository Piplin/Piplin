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
use Piplin\Models\User;

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
            'name'           => 'piplin',
            'nickname'       => 'Piplin',
            'email'          => 'piplin@piplin.com',
            'password'       => bcrypt('piplin'),
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
