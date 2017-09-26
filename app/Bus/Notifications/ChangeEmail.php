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

use Illuminate\Contracts\Translation\Translator;
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
     * @var Translator
     */
    private $translator;

    /**
     * Create a new notification instance.
     *
     * @param string     $token
     * @param Translator $translator
     */
    public function __construct($token, Translator $translator)
    {
        $this->token      = $token;
        $this->translator = $translator;
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
            ->subject($this->translator->trans('emails.confirm_email'))
            ->line($this->translator->trans('emails.change_header'))
            ->line($this->translator->trans('emails.change_below'))
            ->action($this->translator->trans('emails.login_change'), $action)
            ->line($this->translator->trans('emails.change_footer'));
    }
}
