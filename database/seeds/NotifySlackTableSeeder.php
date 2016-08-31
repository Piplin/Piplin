<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\NotifySlack;
use Illuminate\Database\Seeder;

class NotifySlackTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('notify_slacks')->delete();

        NotifySlack::create([
            'name'       => 'Fixhub',
            'channel'    => '#general',
            'icon'       => ':ghost:',
            'webhook'    => 'https://hooks.slack.com/services/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',
            'project_id' => 1,
        ]);
    }
}
