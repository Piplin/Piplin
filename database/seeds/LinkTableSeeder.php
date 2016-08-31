<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Link;
use Illuminate\Database\Seeder;

class LinkTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('links')->delete();

        Link::create([
            'title'       => 'Fixhub',
            'url'         => 'http://www.fixhub.org/',
            'cover'       => '',
            'description' => 'A web deployment system',
        ]);

        Link::create([
            'title'       => '百度',
            'url'         => 'http://www.baidu.com/',
            'cover'       => '',
            'description' => '百度一下，你就知道',
        ]);

        Link::create([
            'title'       => '阿里巴巴',
            'url'         => 'http://www.alibba.com/',
            'cover'       => '',
            'description' => '让天下没有难做的生意',
        ]);

        Link::create([
            'title'       => '腾讯',
            'url'         => 'http://www.qq.com/',
            'cover'       => '',
            'description' => '中国领先的互联网综合服务提供商',
        ]);
    }
}
