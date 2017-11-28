<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification as BaseNotification;
use NotificationChannels\Webhook\WebhookChannel;
use Piplin\Bus\Notifications\Channels\DingtalkChannel;
use Piplin\Models\Hook;

/**
 * Notification class.
 */
abstract class Notification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery hooks.
     *
     * @param  Hook  $hook
     * @return array
     */
    public function via(Hook $hook)
    {
        if ($hook->type === Hook::WEBHOOK) {
            return [WebhookChannel::class];
        } elseif ($hook->type === Hook::DINGTALK) {
            return [DingtalkChannel::class];
        }

        return [$hook->type];
    }
}
