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
use Piplin\Models\SharedFile;

class SharedFileTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('shared_files')->delete();

        SharedFile::create([
            'name'            => 'Storage',
            'file'            => 'storage/',
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
        ]);

        SharedFile::create([
            'name'            => 'Uploads',
            'file'            => '/public/upload/',
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
        ]);

        SharedFile::create([
            'name'            => 'README',
            'file'            => 'README.md',
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
        ]);

        SharedFile::create([
            'name'            => 'LICENSE',
            'file'            => '/LICENSE.md',
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
        ]);
    }
}
