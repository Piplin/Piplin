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
use Piplin\Models\Variable;

class VariableTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('variables')->delete();

        Variable::create([
            'targetable_type' => 'Piplin\\Models\\Project',
            'targetable_id'   => 1,
            'name'            => 'COMPOSER_PROCESS_TIMEOUT',
            'value'           => '3000',
        ]);
    }
}
