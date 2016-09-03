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
$I->am('a guest');
$I->wantTo('test login with invalid credentials');
$I->amOnPage('/auth/login');
$I->fillField('login', 'fixhub.org@gmail.com');
$I->fillField('password', 'test123');
$I->click('button[type=submit]');
$I->assertFalse(Auth::check());
