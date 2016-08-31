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

use Fixhub\Bus\Events\UserWasCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Sends an email when the user has been created.
 */
class SendSignupEmail extends Event implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event handler.
     *
     * @return SendSignupEmail
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserWasCreated $event
     * @return void
     */
    public function handle(UserWasCreated $event)
    {
        $user = $event->user;

        $data = [
            'password' => $event->password,
            'email'    => $user->email,
        ];

        Mail::queueOn(
            'fixhub-low',
            'emails.account',
            $data,
            function (Message $message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject(trans('emails.creation_subject'));
            }
        );
    }
}
