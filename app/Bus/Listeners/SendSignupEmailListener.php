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

use Fixhub\Bus\Events\UserWasCreatedEvent;
use Fixhub\Bus\Notifications\User\UserCreatedNotification;

/**
 * Sends an email when the user has been created.
 */
class SendSignupEmailListener
{
    /**
     * Handle the event.
     *
     * @param  UserWasCreatedEvent $event
     * @return void
     */
    public function handle(UserWasCreatedEvent $event)
    {
        $event->user->notify(new UserCreatedNotification($event->password));
    }
}
