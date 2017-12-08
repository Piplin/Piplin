<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Observers;

use Piplin\Bus\Notifications\Hook\TestNotification;
use Piplin\Models\Hook;

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
