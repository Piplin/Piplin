<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Listeners\Events;

use Fixhub\Bus\Events\DeployFinished;
use Fixhub\Bus\Jobs\MailDeployNotification;
use Fixhub\Bus\Jobs\RequestProjectCheckUrl;
use Fixhub\Bus\Jobs\SlackNotify;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;

/**
 * When a deploy finished, notify the followed user.
 */
class NotifyDeploy extends Event implements ShouldQueue
{
    use InteractsWithQueue, DispatchesJobs;

    /**
     * Create the event handler.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

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

        // Send slack notifications
        foreach ($project->notifySlacks as $notifyslack) {
            if ($notifyslack->failure_only === true && $deployment->isSuccessful()) {
                continue;
            }

            $this->dispatch(new SlackNotify($notifyslack, $deployment->notifySlackPayload()));
        }

        // Send email notification
        $this->dispatch(new MailDeployNotification($project, $deployment));

        // Trigger to check the project urls
        $this->dispatch(new RequestProjectCheckUrl($project->checkUrls));
    }
}
