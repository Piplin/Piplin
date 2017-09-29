<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Notifications\Hook;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Webhook\WebhookMessage;
use Fixhub\Models\Hook;
use Fixhub\Bus\Notifications\Notification;

/**
 * This is the system test notification class.
 */
class TestNotification extends Notification
{
    /**
     * Get the mail representation of the notification.
     *
     * @param Hook $notification
     *
     * @return MailMessage
     */
    public function toMail(Hook $notification)
    {
        return (new MailMessage())
            ->view(['notifications.email', 'notifications.email-plain'], [
                'name' => $notification->name,
            ])
            ->subject(trans('hooks.test_subject'))
            ->line(trans('hooks.test_message'));
    }

    /**
     * Get the slack version of the notification.
     *
     * @param Hook $notification
     *
     * @return SlackMessage
     */
    public function toSlack(Hook $notification)
    {
        return (new SlackMessage())
            ->to($notification->config->channel)
            ->content(trans('hooks.test_message'));
    }

    /**
     * Get the webhook version of the notification.
     *
     * @param Hook $notification
     *
     * @return WebhookMessage
     */
    public function toWebhook(Hook $notification)
    {
        return (new WebhookMessage())
            ->data([
                'message' => trans('hooks.test_message'),
            ])
            ->header('X-Fixhub-Project-Id', $notification->project_id)
            ->header('X-Fixhub-Notification-Id', $notification->id)
            ->header('X-Fixhub-Event', 'notification_test');
    }
}
