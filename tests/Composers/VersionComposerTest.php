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

use Fixhub\Composers\VersionComposer;
use Fixhub\Services\Github\LatestReleaseInterface;
use Fixhub\Test\AbstractTestCase;
use Illuminate\Contracts\View\View;

class VersionComposerTest extends AbstractTestCase
{
    public function testCompose()
    {
        $current = APP_VERSION;

        $release = $this->getMockBuilder(LatestReleaseInterface::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $release->expects($this->once())
                ->method('latest')
                ->willReturn(APP_VERSION);

        $view = $this->getMockBuilder(View::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $view->expects($this->any())
             ->method('with')
             ->withConsecutive(
                 [$this->equalTo('is_outdated'), $this->equalTo(false)],
                 [$this->equalTo('current_version'), $this->equalTo($current)],
                 [$this->equalTo('latest_version'), $this->equalTo($current)]
             );

        $composer = new VersionComposer($release);
        $composer->compose($view);
    }
}
