<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Listeners;

use Fixhub\Bus\Events\DeployFinishedEvent;
use Fixhub\Bus\Notifications\Deployment\DeploymentFailedNotification;
use Fixhub\Bus\Notifications\Deployment\DeploymentSucceededNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;

/**
 * When a deploy finished, notify the followed user.
 */
class NotifyDeployListener implements ShouldQueue
{
    use InteractsWithQueue, DispatchesJobs;

    /**
     * Handle the event.
     *
     * @param  DeployFinishedEvent $event
     * @return void
     */
    public function handle(DeployFinishedEvent $event)
    {
        $project    = $event->deployment->project;
        $deployment = $event->deployment;

        if ($deployment->isAborted()) {
            return;
        }

        $notification = DeploymentFailedNotification::class;
        $event = 'deployment_failure';
        if ($deployment->isSuccessful()) {
            $notification = DeploymentSucceededNotification::class;
            $event = 'deployment_success';
        }

        foreach ($project->hooks->where('enabled', true)->where('on_'. $event, true) as $channel) {
            $channel->notify(new $notification($project, $deployment));
        }
    }
}
