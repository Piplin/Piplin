<?php

namespace Fixhub\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Fixhub\Models\User;

/**
 * Notification sent when changing email.
 */
class ChangeEmail extends Notification implements ShouldQueue
{
    use Queueable;

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
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['mail'];
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