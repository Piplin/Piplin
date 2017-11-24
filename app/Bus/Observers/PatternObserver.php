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

use Piplin\Models\Pattern;

/**
 * Event observer for Pattern model.
 */
class PatternObserver
{

    /**
     * Called when the model is deleting.
     *
     * @param Pattern $pattern
     */
    public function deleting(Pattern $pattern)
    {
        $pattern->commands()->detach();
    }
}
