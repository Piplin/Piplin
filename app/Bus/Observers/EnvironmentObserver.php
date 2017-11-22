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

use Piplin\Models\Environment;

/**
 * Event observer for Environment model.
 */
class EnvironmentObserver
{
    /**
     * Called when the model is deleting.
     *
     * @param Environment $environment
     */
    public function deleting(Environment $environment)
    {
        $environment->commands()->detach();
        $environment->configFiles()->detach();
        $environment->oppositeEnvironments()->detach();
        $environment->servers()->forceDelete();
    }
}
