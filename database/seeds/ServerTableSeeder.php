<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Server;
use Illuminate\Database\Seeder;

class ServerTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('servers')->delete();

        Server::create([
            'name'            => 'Web服务器',
            'ip_address'      => '127.0.0.1',
            'user'            => 'fixhub',
            'path'            => '/var/www/web',
            'targetable_type' => 'Fixhub\\Models\\Environment',
            'targetable_id'   => 1,
            'enabled'         => true,
        ]);

        Server::create([
            'name'            => 'API服务器',
            'ip_address'      => '192.168.75.20',
            'user'            => 'fixhub',
            'path'            => '/var/www',
            'targetable_type' => 'Fixhub\\Models\\Environment',
            'targetable_id'   => 2,
            'enabled'         => true,
        ]);

        Server::create([
            'name'            => '数据库服务器',
            'ip_address'      => '192.168.75.21',
            'user'            => 'fixhub',
            'path'            => '/home/fixhub',
            'targetable_type' => 'Fixhub\\Models\\Environment',
            'targetable_id'   => 2,
            'enabled'         => false,
        ]);

        Server::create([
            'name'            => '数据库服务器',
            'ip_address'      => 'localhost',
            'user'            => 'fixhub',
            'path'            => '/var/www/db',
            'targetable_type' => 'Fixhub\\Models\\Cabinet',
            'targetable_id'   => 1,
            'enabled'         => true,
        ]);
    }
}
