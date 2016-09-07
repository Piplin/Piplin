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

use Fixhub\Bus\Jobs\NotifySlackJob;
use Fixhub\Bus\Events\HasSlackPayloadInterface;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Event handler class for heartbeat recovery.
 **/
class NotifyHeartbeat extends Event
{
    use DispatchesJobs;

    /**
     * Create the event listener.
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
     * @param  Event $event
     * @return void
     */
    public function handle(HasSlackPayloadInterface $event)
    {
        foreach ($event->heartbeat->project->notifySlacks as $notifyslack) {
            $this->dispatch(new NotifySlackJob($notifyslack, $event->notifySlackPayload()));
        }
    }
}
