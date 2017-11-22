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

use Piplin\Bus\Events\EmailChangeRequestedEvent;
use Piplin\Bus\Notifications\User\ChangeEmailNotification;

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
