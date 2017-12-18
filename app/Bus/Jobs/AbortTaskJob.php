<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Jobs;

use Illuminate\Support\Facades\Cache;
use Piplin\Models\Task;

/**
 * A class to handle caching the abort request.
 */
class AbortTaskJob extends Job
{
    /**
     * @var Task
     */
    private $task;

    const CACHE_KEY_PREFIX = 'piplin:cancel-deploy:';

    /**
     * Create a new job instance.
     *
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Cache::put(self::CACHE_KEY_PREFIX . $this->task->id, time(), 3600);
    }
}
