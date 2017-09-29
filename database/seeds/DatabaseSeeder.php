<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->call(UserTableSeeder::class);
        $this->call(ProjectGroupTableSeeder::class);
        $this->call(KeyTableSeeder::class);
        $this->call(ProjectTableSeeder::class);
        $this->call(EnvironmentTableSeeder::class);
        $this->call(ServerTableSeeder::class);
        $this->call(DeploymentTableSeeder::class);
        $this->call(CommandTableSeeder::class);
        $this->call(DeployTemplateTableSeeder::class);
        $this->call(VariableTableSeeder::class);
        $this->call(SharedFileTableSeeder::class);
        $this->call(ConfigFileTableSeeder::class);
        $this->call(TipTableSeeder::class);
        $this->call(LinkTableSeeder::class);
    }
}
