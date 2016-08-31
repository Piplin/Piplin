<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Listeners\Events;

use Fixhub\Bus\Events\EmailChangeRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Request email change handler.
 */
class EmailChangeConfirmation extends Event implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  EmailChangeRequested $event
     * @return void
     */
    public function handle(EmailChangeRequested $event)
    {
        $user = $event->user;

        $data = [
            'email' => $user->email,
            'name'  => $user->name,
            'token' => $user->requestEmailToken(),
        ];

        Mail::queueOn(
            'fixhub-low',
            'emails.change_email',
            $data,
            function (Message $message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject(trans('emails.confirm_email'));
            }
        );
    }
}
