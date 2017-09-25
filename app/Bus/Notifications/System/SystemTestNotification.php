<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Notifications\System;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use Fixhub\Models\NotifySlack;

/**
 * This is the system test notification class.
 */
class SystemTestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  NotifySlack $notification
     * @return array
     */
    public function via(NotifySlack $notification)
    {
        return ['slack'];
    }

    /**
     * Get the slack version of the notification.
     *
     * @param Channel $notification
     *
     * @return SlackMessage
     */
    public function toSlack(NotifySlack $notification)
    {
        return (new SlackMessage())
            ->to($notification->channel)
            ->content($this->translator->trans('notifySlacks.test_message'));
    }
}
