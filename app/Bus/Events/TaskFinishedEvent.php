<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Events;

use Illuminate\Queue\SerializesModels;
use Piplin\Bus\Events\Event;
use Piplin\Models\Task;
use Piplin\Models\Project;

/**
 * Task finished event.
 */
class TaskFinishedEvent extends Event
{
    use SerializesModels;

    public $task;

    /**
     * Create a new event instance.
     *
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }
}
