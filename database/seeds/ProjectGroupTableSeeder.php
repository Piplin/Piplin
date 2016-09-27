<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\ProjectGroup;
use Illuminate\Database\Seeder;

class ProjectGroupTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('project_groups')->delete();

        ProjectGroup::create([
                'name' => 'Fixhub',
        ]);

        ProjectGroup::create([
                'name' => 'Demo',
        ]);
    }
}
