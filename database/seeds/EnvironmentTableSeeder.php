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
use Piplin\Models\Environment;

class EnvironmentTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('environments')->delete();

        $environment = Environment::create([
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'name'            => 'Staging',
            'description'     => 'Staging',
        ]);

        $environment->cabinets()->sync($environment->id);

        Environment::create([
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'name'            => 'QA',
            'default_on'      => false,
            'description'     => 'QA',
        ]);

        Environment::create([
            'targetable_type' => 'Piplin\\Models\\ProjectTemplate',
            'targetable_id'   => 1,
            'name'            => 'Staging',
            'description'     => 'Staging',
        ]);

        Environment::create([
            'targetable_type' => 'Piplin\\Models\\ProjectTemplate',
            'targetable_id'   => 1,
            'name'            => 'QA',
            'default_on'      => false,
            'description'     => 'QA',
        ]);

        Environment::create([
            'targetable_type' => 'Piplin\\Models\\ProjectTemplate',
            'targetable_id'   => 1,
            'name'            => 'Production',
            'description'     => 'Production',
        ]);
    }
}
