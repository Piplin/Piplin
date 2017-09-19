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
        $name = 'phecho';
        $email = 'phecho@fixhub.org';
        $password ='password';

        User::create(['name' => $name, 'email' => $email, 'password' => $password]);

        $this->tester->seeRecord('users', ['name' => $name, 'email' => $email]);
    }

    public function testIfModelReturnsAdminUserEmail()
    {
        // access model
        $user = User::where('name', 'fixhub')->first();

        $this->assertEquals('fixhub@fixhub.org', $user->email);
    }
}
