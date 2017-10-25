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

use Fixhub\Models\Hook;
use Fixhub\Bus\Notifications\Hook\TestNotification;

/**
 * Event observer for Hook model.
 */
class HookObserver
{
    /**
     * Called when the model is saved.
     *
     * @param Hook $hook
     */
    public function saved(Hook $hook)
    {
        $hook->notify(new TestNotification());
    }
}
