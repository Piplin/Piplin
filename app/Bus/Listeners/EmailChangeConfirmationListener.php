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

use Fixhub\Bus\Events\EmailChangeRequestedEvent;
use Fixhub\Bus\Notifications\User\ChangeEmailNotification;

/**
 * Request email change handler.
 */
class EmailChangeConfirmationListener
{
    /**
     * Handle the event.
     *
     * @param  EmailChangeRequestedEvent $event
     * @return void
     */
    public function handle(EmailChangeRequestedEvent $event)
    {
        $token = $event->user->requestEmailToken();
        $event->user->notify(new ChangeEmailNotification($token));
    }
}
