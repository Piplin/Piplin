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

use Illuminate\Notifications\Messages\MailMessage;
use Fixhub\Models\User;

/**
 * Notification sent when changing email.
 */
class ChangeEmail extends Notification
{
    /**
     * @var string
     */
    private $token;

    /**
     * Create a new notification instance.
     *
     * @param string     $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $user
     *
     * @return MailMessage
     */
    public function toMail(User $user)
    {
        $action = route('profile.confirm-change-email', ['token' => $this->token]);

        return (new MailMessage())
            ->view(['notifications.email', 'notifications.email-plain'], [
                'name' => $user->name,
            ])
            ->subject(trans('emails.confirm_email'))
            ->line(trans('emails.change_header'))
            ->line(trans('emails.change_below'))
            ->action(trans('emails.login_change'), $action)
            ->line(trans('emails.change_footer'));
    }
}
