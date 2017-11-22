<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Piplin\Models\User;

class UserTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        //
    }

    protected function _after()
    {
        //
    }

    public function testRegister()
    {
        $name     = 'test';
        $email    = 'test@piplin.com';
        $password = 'password';

        User::create(['name' => $name, 'email' => $email, 'password' => $password]);

        $this->tester->seeRecord('users', ['name' => $name, 'email' => $email]);
    }

    public function testIfModelReturnsAdminUserEmail()
    {
        // access model
        $user = User::where('name', 'piplin')->first();

        $this->assertEquals('piplin@piplin.com', $user->email);
    }
}
