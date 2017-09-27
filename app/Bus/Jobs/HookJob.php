<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Jobs;

use Carbon\Carbon;
use Fixhub\Models\Hook;
use Httpful\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Fixhub\Bus\Notifications\Hook\TestNotification;
/**
 * Sends notification to slack.
 */
class HookJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $payload;
    private $hook;
    private $timeout;

    /**
     * Create a new command instance.
     *
     * @param Hook $hook
     * @param array $payload
     * @param int $timeout
     */
    public function __construct(Hook $hook)
    {
        $this->hook = $hook;
    }

    /**
     * Execute the command.
     */
    public function handle()
    {
        $this->hook->notify(new TestNotification());
    }
}
