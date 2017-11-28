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
use Piplin\Models\Server;

class ServerTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('servers')->delete();

        Server::create([
            'name'            => 'Web服务器',
            'ip_address'      => '127.0.0.1',
            'user'            => 'piplin',
            'targetable_type' => 'Piplin\\Models\\Environment',
            'targetable_id'   => 1,
            'enabled'         => true,
        ]);

        Server::create([
            'name'            => 'API服务器',
            'ip_address'      => '192.168.75.20',
            'user'            => 'piplin',
            'targetable_type' => 'Piplin\\Models\\Environment',
            'targetable_id'   => 2,
            'enabled'         => true,
        ]);

        Server::create([
            'name'            => '数据库服务器',
            'ip_address'      => '192.168.75.21',
            'user'            => 'piplin',
            'targetable_type' => 'Piplin\\Models\\Environment',
            'targetable_id'   => 2,
            'enabled'         => false,
        ]);

        Server::create([
            'name'            => '数据库服务器',
            'ip_address'      => 'localhost',
            'user'            => 'piplin',
            'targetable_type' => 'Piplin\\Models\\Cabinet',
            'targetable_id'   => 1,
            'enabled'         => true,
        ]);
    }
}
