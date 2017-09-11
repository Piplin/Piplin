<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Heartbeat;
use Illuminate\Database\Seeder;

class HeartbeatTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('heartbeats')->delete();
        /*
        Heartbeat::create([
            'name'       => 'My Cron Job',
            'project_id' => 1,
            'interval'   => 30,
        ]);
        */
    }
}
