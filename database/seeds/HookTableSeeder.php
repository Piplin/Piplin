<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Seeder;
use Piplin\Models\Hook;

class HookTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('hooks')->delete();

        Hook::create([
            'name'                  => 'Piplin',
            'type'                  => 'slack',
            'enabled'               => true,
            'config'                => [
                'channel' => '#piplin',
                'webhook' => 'https://hooks.slack.com/services/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',
                'icon'    => ':ghost:',
            ],
            'on_deployment_success' => true,
            'on_deployment_failure' => true,
            'project_id'            => 1,
        ]);
    }
}
