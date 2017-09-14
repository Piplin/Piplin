<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Command;
use Fixhub\Models\DeployTemplate;
use Illuminate\Database\Seeder;

class DeployTemplateTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('deploy_templates')->delete();

        $laravel = DeployTemplate::create([
            'name' => 'Laravel',
        ]);

        DeployTemplate::create([
            'name' => 'Wordpress',
        ]);

        Command::create([
            'name'            => 'Down',
            'script'          => 'php {{ release_path }}/artisan down',
            'targetable_type' => 'Fixhub\\Models\\DeployTemplate',
            'targetable_id'   => $laravel->id,
            'user'            => '',
            'step'            => Command::BEFORE_ACTIVATE,
        ]);

        Command::create([
            'name'            => 'Run Migrations',
            'script'          => 'php {{ release_path }}/artisan migrate --force',
            'targetable_type' => 'Fixhub\\Models\\DeployTemplate',
            'targetable_id'   => $laravel->id,
            'user'            => '',
            'step'            => Command::BEFORE_ACTIVATE,
        ]);

        Command::create([
            'name'            => 'Up',
            'script'          => 'php {{ release_path }}/artisan up',
            'targetable_type' => 'Fixhub\\Models\\DeployTemplate',
            'targetable_id'   => $laravel->id,
            'user'            => '',
            'step'            => Command::BEFORE_ACTIVATE,
        ]);
    }
}
