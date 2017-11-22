<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Seeder;
use Piplin\Models\User;

class UserTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker\Factory::create('en_GB');

        for ($i = 1; $i < 10; $i++) {
            User::create([
                'name'           => $faker->userName,
                'nickname'       => $faker->firstName . ' ' . $faker->lastName,
                'email'          => $faker->safeEmail,
                'password'       => bcrypt($faker->password),
                'remember_token' => str_random(10),
            ]);
        }
    }
}
