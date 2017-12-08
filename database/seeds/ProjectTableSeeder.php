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
use Piplin\Models\Project;

class ProjectTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('projects')->delete();

        $project = Project::create([
            'name'            => 'Piplin',
            'hash'            => str_random(60),
            'repository'      => 'https://github.com/piplin/piplin.git',
            'url'             => 'http://piplin.com',
            'targetable_type' => 'Piplin\\Models\\ProjectGroup',
            'targetable_id'   => 1,
            'deploy_path'     => '/var/www/web',
            'key_id'          => 1,
            'last_run'        => null,
            'build_url'       => 'https://img.shields.io/travis/Piplin/Piplin/master.svg?style=flat-square',
        ]);

        $project->members()->attach([1]);
    }
}
