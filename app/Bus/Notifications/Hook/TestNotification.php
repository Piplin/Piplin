<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Notifications\Hook;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use NotificationChannels\Webhook\WebhookMessage;
use Piplin\Bus\Notifications\Notification;
use Piplin\Models\Hook;

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
            ->line(trans('hooks.test_message', ['app_url' => config('app.url')]));
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
            ->content(trans('hooks.test_message', ['app_url' => config('app.url')]));
    }

    /**
     * Get the dingtalk version of the notification.
     *
     * @param Hook $notification
     *
     * @return WebhookMessage
     */
    public function toDingtalk(Hook $notification)
    {
        $atMobiles = !empty($notification->config->at_mobiles) ? explode(',', $notification->config->at_mobiles) : [];

        return (new WebhookMessage())
            ->data([
                'msgtype' => 'text',
                'text'    => [
                    'content' => trans('hooks.test_message', ['app_url' => config('app.url')]),
                ],
                'at' => [
                    'atMobiles' => $atMobiles,
                    'isAtAll'   => !!$notification->config->is_at_all,
                ],
            ])
            ->header('Content-Type', 'application/json;charset=utf-8')
            ->header('X-Piplin-Project-Id', $notification->project_id)
            ->header('X-Piplin-Notification-Id', $notification->id)
            ->header('X-Piplin-Event', 'notification_test');
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
                'message' => trans('hooks.test_message', ['app_url' => config('app.url')]),
            ])
            ->header('X-Piplin-Project-Id', $notification->project_id)
            ->header('X-Piplin-Notification-Id', $notification->id)
            ->header('X-Piplin-Event', 'notification_test');
    }
}
