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

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification as BaseNotification;
use Fixhub\Models\NotifySlack;

/**
 * Notification class.
 */
abstract class Notification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  Model $model
     * @return array
     */
    public function via($model)
    {
        if ($model instanceof NotifySlack) {
            return ['slack'];
        } else {
            return ['mail'];
        }
    }
}
