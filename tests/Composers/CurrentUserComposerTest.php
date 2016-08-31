<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Test\Composers;

use Fixhub\Composers\CurrentUserComposer;
use Fixhub\Test\AbstractTestCase;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CurrentUserComposerTest extends AbstractTestCase
{
    public function testCompose()
    {
        $expected_user = 123456;

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($expected_user);

        $view = $this->getMockBuilder(View::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $view->expects($this->any())
             ->method('with')
             ->withConsecutive(
                 [$this->equalTo('current_user'), $this->equalTo($expected_user)]
             );

        $composer = new CurrentUserComposer;
        $composer->compose($view);
    }
}
