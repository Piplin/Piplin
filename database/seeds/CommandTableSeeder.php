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
use Illuminate\Database\Seeder;

class CommandTableSeeder extends Seeder
{
    private function getScript()
    {
        return <<< EOD
echo "Release {{ release }}"
echo "Release Path {{ release_path }}"
echo "Project Path {{ project_path }}"
echo "Branch {{ branch }}"
echo "SHA {{ sha }}"
echo "Short SHA {{ short_sha }}"
echo "Deployer email {{ deployer_email }}"
echo "Deployer name {{ deployer_name }}"
echo "Committer email {{ committer_email }}"
echo "Committer name {{ committer_name }}"
echo "Server user \$(whoami)"
EOD;
    }

    public function run()
    {
        DB::table('commands')->delete();

        Command::create([
            'name'       => 'Before Create New Release',
            'script'     => $this->getScript(),
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
            'user'       => '',
            'step'       => Command::BEFORE_CLONE,
            'optional'   => true,
        ])->servers()->attach([1, 2]);

        Command::create([
            'name'       => 'After Create New Release',
            'script'     => $this->getScript(),
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
            'user'       => '',
            'step'       => Command::AFTER_CLONE,
        ])->servers()->attach([1, 2]);

        Command::create([
            'name'       => 'Before Install',
            'script'     => $this->getScript(),
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
            'user'       => '',
            'step'       => Command::BEFORE_INSTALL,
        ])->servers()->attach([1, 2]);

        Command::create([
            'name'       => 'After Install',
            'script'     => $this->getScript(),
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
            'user'       => '',
            'step'       => Command::AFTER_INSTALL,
        ])->servers()->attach([1, 2]);

        Command::create([
            'name'       => 'Before Activate',
            'script'     => $this->getScript(),
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
            'user'       => '',
            'step'       => Command::BEFORE_ACTIVATE,
        ])->servers()->attach([1, 2]);

        Command::create([
            'name'       => 'After Activate',
            'script'     => $this->getScript(),
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
            'user'       => '',
            'step'       => Command::AFTER_ACTIVATE,
        ])->servers()->attach([1, 2]);

        Command::create([
            'name'       => 'Before Purge',
            'script'     => $this->getScript(),
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
            'user'       => '',
            'step'       => Command::BEFORE_PURGE,
        ])->servers()->attach([1, 2]);

        Command::create([
            'name'       => 'After Purge',
            'script'     => $this->getScript(),
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
            'user'       => '',
            'step'       => Command::AFTER_PURGE,
            'optional'   => true,
        ])->servers()->attach([1, 2]);
    }
}
