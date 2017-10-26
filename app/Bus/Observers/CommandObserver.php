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

use Fixhub\Models\Command;

/**
 * Event observer for Command model.
 */
class CommandObserver
{
    /**
     * Called when the model is deleting.
     *
     * @param Command $command
     */
    public function deleting(Command $command)
    {
        $command->environments()->detach();
    }
}
