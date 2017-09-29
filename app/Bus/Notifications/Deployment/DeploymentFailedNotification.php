<?php

namespace Fixhub\Bus\Notifications\Deployment;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use NotificationChannels\Webhook\WebhookMessage;
use Fixhub\Models\Hook;

/**
 * Notification sent when a deployment succeeds.
 */
class DeploymentFailedNotification extends DeploymentFinishedNotification
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
        return $this->buildMailMessage(
            'hooks.deployment_failed_email_subject',
            'hooks.deployment_failed_email_message',
            $notification
        )->error();
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
        return $this->buildSlackMessage(
            'hooks.deployment_failed_slack_message',
            $notification
        )->error();
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
        return $this->buildWebhookMessage('deployment_failed', $notification);
    }
}
