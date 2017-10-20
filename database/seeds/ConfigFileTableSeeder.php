<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\ConfigFile;
use Illuminate\Database\Seeder;

class ConfigFileTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('config_files')->delete();

        $config_file = ConfigFile::create([
            'name'    => 'Configuration',
            'path'    => '.env',
            'content' => 'APP_ENV=local
APP_DEBUG=true
APP_KEY=KkaOy5AZuzQ8ILAs6EwEYnK4VZVZJvNT
APP_URL=http://fixhub.app
APP_TIMEZONE=UTC
APP_LOCALE=en
APP_THEME=green
APP_LOG=daily

JWT_SECRET=zLBooByVMcfVWJYaSEKr7iKHIMluVBAl

SOCKET_URL=http://fixhub.app
SOCKET_PORT=6001

DB_TYPE=mysql
DB_HOST=localhost
DB_DATABASE=fixhub
DB_USERNAME=homestead
DB_PASSWORD=secret

MAIL_DRIVER=mail
MAIL_FROM_NAME=Fixhub
MAIL_FROM_ADDRESS=fixhub@fixhub.app

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

QUEUE_DRIVER=beanstalkd
QUEUE_HOST=localhost

CACHE_DRIVER=file
SESSION_DRIVER=file
IMAGE_DRIVER=gd
',
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
        ])->environments()->sync([1,2]);
    }
}
