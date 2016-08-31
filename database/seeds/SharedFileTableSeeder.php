<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\SharedFile;
use Illuminate\Database\Seeder;

class SharedFileTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('shared_files')->delete();

        SharedFile::create([
            'name'       => 'Storage',
            'file'       => 'storage/',
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
        ]);

        SharedFile::create([
            'name'       => 'Uploads',
            'file'       => '/public/upload/',
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
        ]);

        SharedFile::create([
            'name'       => 'README',
            'file'       => 'README.md',
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
        ]);

        SharedFile::create([
            'name'       => 'LICENSE',
            'file'       => '/LICENSE.md',
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id' => 1,
        ]);
    }
}
