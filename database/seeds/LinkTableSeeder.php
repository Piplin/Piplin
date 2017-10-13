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
            'title'       => 'Github',
            'url'         => 'https://github.com/Fixhub/Fixhub',
            'cover'       => '',
            'description' => 'Fixhub on Github',
        ]);
    }
}
