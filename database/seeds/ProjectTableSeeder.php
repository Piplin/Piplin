<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Project;
use Illuminate\Database\Seeder;

class ProjectTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('projects')->delete();

        $project = Project::create([
            'name'            => 'Fixhub',
            'hash'            => str_random(60),
            'repository'      => 'https://github.com/fixhub/fixhub.git',
            'url'             => 'http://fixhub.org',
            'targetable_type' => 'Fixhub\\Models\\ProjectGroup',
            'targetable_id'   => 1,
            'key_id'          => 1,
            'last_run'        => null,
            'build_url'       => 'https://img.shields.io/travis/Fixhub/Fixhub/master.svg?style=flat-square',
        ]);

        $project->members()->attach([1]);
    }
}
