<?php 

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$I = new FunctionalTester($scenario);
$I->wantTo('login as a user');
$I->haveRecord('users', [
    'name'       => 'JohnSmith',
    'email'      => 'john@smith.com',
    'password'   => bcrypt('password'),
]);
$I->amOnPage('/login');
$I->fillField('login', 'JohnSmith');
$I->fillField('password', 'password');
$I->click('button[type=submit]');
$I->amOnPage('/');
$I->seeAuthentication();
$I->see('JohnSmith', '.user-menu');
