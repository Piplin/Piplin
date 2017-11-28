<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Piplin\Bus\Events\TaskFinishedEvent;
use Piplin\Bus\Notifications\Task\TaskFailedNotification;
use Piplin\Bus\Notifications\Task\TaskSucceededNotification;

/**
 * When a deploy finished, notify the followed user.
 */
class NotifyDeployListener implements ShouldQueue
{
    use InteractsWithQueue, DispatchesJobs;

    /**
     * Handle the event.
     *
     * @param  TaskFinishedEvent $event
     * @return void
     */
    public function handle(TaskFinishedEvent $event)
    {
        $task    = $event->task;
        $project = $event->task->project;

        if ($task->isAborted()) {
            return;
        }

        $notification = TaskFailedNotification::class;
        $event        = 'deployment_failure';
        if ($task->isSuccessful()) {
            $notification = TaskSucceededNotification::class;
            $event        = 'deployment_success';
        }

        foreach ($project->hooks->where('enabled', true)->where('on_' . $event, true) as $hook) {
            $hook->notify(new $notification($project, $task));
        }
    }
}
