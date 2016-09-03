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
$I->wantTo('open index page of site');
$I->amOnPage('/');
$I->see('Fixhub', 'title');
