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

use Fixhub\Bus\Events\DeployFinished;
use Fixhub\Bus\Notifications\Deployment\DeploymentFailed;
use Fixhub\Bus\Notifications\Deployment\DeploymentSucceeded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;

/**
 * When a deploy finished, notify the followed user.
 */
class NotifyDeploy implements ShouldQueue
{
    use InteractsWithQueue, DispatchesJobs;

    /**
     * Handle the event.
     *
     * @param  DeployFinished $event
     * @return void
     */
    public function handle(DeployFinished $event)
    {
        $project    = $event->deployment->project;
        $deployment = $event->deployment;

        if ($deployment->isAborted()) {
            return;
        }

        $notification = DeploymentFailed::class;
        $event = 'deployment_failure';
        if ($deployment->isSuccessful()) {
            $notification = DeploymentSucceeded::class;
            $event = 'deployment_success';
        }

        foreach ($project->hooks->where('enabled', true)->where('on_'. $event, true) as $channel) {
            $channel->notify(new $notification($project, $deployment));
        }
    }
}
