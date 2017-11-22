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
use Piplin\Models\ProjectGroup;

class ProjectGroupTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('project_groups')->delete();

        ProjectGroup::create([
                'name' => 'Piplin',
        ]);

        ProjectGroup::create([
                'name' => 'Demo',
        ]);
    }
}
