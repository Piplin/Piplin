<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Setting;
use Illuminate\Database\Seeder;

class SettingTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('settings')->delete();

        Setting::create([
            'name' => 'app_name',
            'value' => 'Fixhub',
        ]);

        Setting::create([
            'name' => 'app_about',
            'value' => 'A web deployment system.',
        ]);
    }
}
