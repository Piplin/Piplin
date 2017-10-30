<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Services\Executors;

interface ExecutorInterface
{
    /**
     * @param ServerLog[] $logs
     * @param Command[]   $commands
     */
    public function run($logs, $commands);
}
