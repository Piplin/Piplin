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
use Piplin\Models\Command;

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
echo "Author email {{ author_email }}"
echo "Author name {{ author_name }}"
echo "Committer email {{ committer_email }}"
echo "Committer name {{ committer_name }}"
echo "Server user \$(whoami)"
EOD;
    }

    public function run()
    {
        DB::table('commands')->delete();

        Command::create([
            'name'            => 'Before Create New Release',
            'script'          => $this->getScript(),
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'user'            => '',
            'step'            => Command::BEFORE_CLONE,
            'optional'        => true,
        ])->environments()->attach([1, 2]);

        Command::create([
            'name'            => 'After Create New Release',
            'script'          => $this->getScript(),
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'user'            => '',
            'step'            => Command::AFTER_CLONE,
        ])->environments()->attach([1, 2]);

        Command::create([
            'name'            => 'Before Install',
            'script'          => $this->getScript(),
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'user'            => '',
            'step'            => Command::BEFORE_INSTALL,
        ])->environments()->attach([1, 2]);

        Command::create([
            'name'            => 'After Install',
            'script'          => $this->getScript(),
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'user'            => '',
            'step'            => Command::AFTER_INSTALL,
        ])->environments()->attach([1, 2]);

        Command::create([
            'name'            => 'Before Activate',
            'script'          => $this->getScript(),
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'user'            => '',
            'step'            => Command::BEFORE_ACTIVATE,
        ])->environments()->attach([1, 2]);

        Command::create([
            'name'            => 'After Activate',
            'script'          => $this->getScript(),
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'user'            => '',
            'step'            => Command::AFTER_ACTIVATE,
        ])->environments()->attach([1, 2]);

        Command::create([
            'name'            => 'Before Purge',
            'script'          => $this->getScript(),
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'user'            => '',
            'step'            => Command::BEFORE_PURGE,
        ])->environments()->attach([1, 2]);

        Command::create([
            'name'            => 'After Purge',
            'script'          => $this->getScript(),
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'user'            => '',
            'step'            => Command::AFTER_PURGE,
            'optional'        => true,
        ])->environments()->attach([1, 2]);
    }
}
