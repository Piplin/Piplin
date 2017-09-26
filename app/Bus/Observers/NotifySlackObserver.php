<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Observers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Fixhub\Models\NotifySlack;
use Fixhub\Bus\Jobs\NotifySlackJob;
use Fixhub\Bus\Notifications\SystemTestNotification;

/**
 * Event observer for NotifySlack model.
 */
class NotifySlackObserver
{
    use DispatchesJobs;

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
     * Called when the model is saved.
     *
     * @param NotifySlack $notification
     */
    public function saved(NotifySlack $notification)
    {
        // please fix me
        //$notification->notify(new SystemTestNotification($this->translator));
        $this->dispatch(new NotifySlackJob($notification, [
            'text' => trans('notifySlacks.test_message'),
        ]));
    }
}
