<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Console\Commands;

use Carbon\Carbon;
use Fixhub\Bus\Events\HeartbeatMissed;
use Fixhub\Models\Heartbeat;
use Illuminate\Console\Command;

/**
 * Checks that any expected heartbeats have checked-in.
 */
class CheckHeartbeats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixhub:heartbeats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks that any expected heartbeats have checked-in';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Heartbeat::chunk(10, function ($heartbeats) {
            foreach ($heartbeats as $heartbeat) {
                $last_heard_from = $heartbeat->last_activity;
                if (!$last_heard_from) {
                    $last_heard_from = $heartbeat->created_at;
                }

                $missed = $heartbeat->missed + 1;

                $next_time = $last_heard_from->addMinutes($heartbeat->interval * $missed);

                if (Carbon::now()->gt($next_time)) {
                    $heartbeat->status = Heartbeat::MISSING;
                    $heartbeat->missed = $missed;
                    $heartbeat->save();

                    event(new HeartbeatMissed($heartbeat));
                }
            }
        });
    }
}
