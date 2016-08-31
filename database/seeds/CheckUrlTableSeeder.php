<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\CheckUrl;
use Illuminate\Database\Seeder;

class CheckUrlTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('check_urls')->delete();

        CheckUrl::create([
            'title'      => 'Fixhub',
            'url'        => 'http://fixhub.app',
            'project_id' => 1,
            'period'     => 10,
        ]);
    }
}
