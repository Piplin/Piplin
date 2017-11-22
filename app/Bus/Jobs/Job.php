<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\Queue;

/**
 * Generic Job class.
 */
abstract class Job
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "onQueue" and "delay" queue helper methods.
    |
    */

    use Queueable;

    /**
     * Overwrite the queue method to push to a different queue.
     *
     * @param Queue $queue
     * @param Job   $command
     */
    public function queue(Queue $queue, $command)
    {
        $queue->pushOn('piplin-low', $command);
    }
}
