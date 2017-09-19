<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Environment;
use Illuminate\Database\Seeder;

class EnvironmentTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('environments')->delete();

        Environment::create([
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id'   => 1,
            'name'            => 'Staging',
            'description'     => 'Staging',
        ]);

        Environment::create([
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id'   => 1,
            'name'            => 'QA',
            'description'     => 'QA',
        ]);

        Environment::create([
            'targetable_type' => 'Fixhub\\Models\\DeployTemplate',
            'targetable_id'   => 1,
            'name'            => 'Staging',
            'description'     => 'Staging',
        ]);

        Environment::create([
            'targetable_type' => 'Fixhub\\Models\\DeployTemplate',
            'targetable_id'   => 1,
            'name'            => 'QA',
            'description'     => 'QA',
        ]);

        Environment::create([
            'targetable_type' => 'Fixhub\\Models\\DeployTemplate',
            'targetable_id'   => 1,
            'name'            => 'Production',
            'description'     => 'Production',
        ]);
    }
}
