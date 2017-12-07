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

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Piplin\Services\Scripts\Runner as Process;

/**
 * Sync config file to specified environments.
 */
class SyncConfiFileJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 100;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
