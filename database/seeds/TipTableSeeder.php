<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Fixhub\Models\Tip;
use Illuminate\Database\Seeder;

class TipTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('tips')->delete();

        Tip::create([
            'body'   => '心若没有栖息的地方，到哪里都是在流浪!',
            'status' => 0,
        ]);

        Tip::create([
            'body'   => '猪有猪的思想，人有人的思想。如果猪有人的思想，那它就不是猪了——是八戒!',
            'status' => 0,
        ]);

        Tip::create([
            'body'   => '每个人至少拥有一个梦想，有一个理由去坚强。',
            'status' => 0,
        ]);
    }
}
