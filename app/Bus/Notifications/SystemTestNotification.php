<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Notifications;
    
use Illuminate\Notifications\Messages\SlackMessage;
use Fixhub\Models\NotifySlack;

/**
 * This is the system test notification class.
 */
class SystemTestNotification extends Notification
{
    /**
     * Get the slack version of the notification.
     *
     * @param NotifySlack $notification
     *
     * @return SlackMessage
     */
    public function toSlack(NotifySlack $notification)
    {
        return (new SlackMessage())
            ->to($notification->channel)
            ->content(trans('notifySlacks.test_message'));
    }
}
