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
            'name'        => 'Web服务器',
            'ip_address'  => '192.168.75.19',
            'user'        => 'fixhub',
            'path'        => '/var/www',
            'project_id'  => 1,
            'deploy_code' => true,
        ]);

        Server::create([
            'name'        => 'API服务器',
            'ip_address'  => '192.168.75.20',
            'user'        => 'fixhub',
            'path'        => '/var/www',
            'project_id'  => 1,
            'deploy_code' => true,
        ]);

        Server::create([
            'name'        => '数据库服务器',
            'ip_address'  => '192.168.75.21',
            'user'        => 'fixhub',
            'path'        => '/home/fixhub',
            'project_id'  => 1,
            'deploy_code' => false,
        ]);
    }
}
