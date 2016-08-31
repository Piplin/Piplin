<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Test\Functional;

use Fixhub\Test\AbstractTestCase;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * This is the command test class.
 */
class CommandTest extends AbstractTestCase
{
    use DatabaseMigrations;

    public function testMigrations()
    {
        $this->assertSame(0, $this->app->make(Kernel::class)->call('migrate', ['--force' => true]));
    }

    public function testSeed()
    {
        $this->assertSame(0, $this->app->make(Kernel::class)->call('db:seed'));
    }
}
