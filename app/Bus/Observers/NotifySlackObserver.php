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
use Fixhub\Models\NotifySlack;
use Fixhub\Bus\Notifications\System\SystemTestNotification;

/**
 * Event observer for NotifySlack model.
 */
class NotifySlackObserver
{
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
     * @param NotifySlack $notify_slack
     */
    public function saved(NotifySlack $notify_slack)
    {
        $notify_slack->notify(new SystemTestNotification($this->translator));
    }
}