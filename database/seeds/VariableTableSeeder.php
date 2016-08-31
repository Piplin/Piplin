<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Variable;
use Illuminate\Database\Seeder;

class VariableTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('variables')->delete();

        Variable::create([
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id'   => 1,
            'name'            => 'COMPOSER_PROCESS_TIMEOUT',
            'value'           => '3000',
        ]);
    }
}
