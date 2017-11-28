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
use Piplin\Models\Cabinet;

class CabinetTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('cabinets')->delete();

        Cabinet::create([
            'name'        => 'Database',
            'description' => 'Database Servers',
        ]);
    }
}
