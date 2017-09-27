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

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification as BaseNotification;
use Fixhub\Models\Hook;
use NotificationChannels\Webhook\WebhookChannel;

/**
 * Notification class.
 */
abstract class Notification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery hooks.
     *
     * @param  Hook $hook
     * @return array
     */
    public function via(Hook $hook)
    {
        if ($hook->type === Hook::WEBHOOK) {
            return [WebhookChannel::class];
        }

        return [$hook->type];
    }
}
