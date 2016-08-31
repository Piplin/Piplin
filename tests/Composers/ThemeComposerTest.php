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

use Fixhub\Composers\ThemeComposer;
use Fixhub\Test\AbstractTestCase;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ThemeComposerTest extends AbstractTestCase
{
    public function testCompose()
    {
        $expected_theme    = config('fixhub.theme');
        $expected_language = config('app.locale');

        $view = $this->getMockBuilder(View::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $view->expects($this->any())
             ->method('with')
             ->withConsecutive(
                 [$this->equalTo('theme'), $this->equalTo($expected_theme)],
                 [$this->equalTo('language'), $this->equalTo($expected_language)]
             );

        $composer = new ThemeComposer;
        $composer->compose($view);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn((object) ['skin' => 'pink', 'language' => 'zh-CN']);

        $composer->compose($view);
    }
}
