<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Piplin\Models\Environment;
use Piplin\Models\Project;
use Piplin\Models\Server;

class AlterProjectsTableAddDeployPath extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('deploy_path')->default('')->after('targetable_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $servers = Server::withTrashed()->get();
            foreach ($servers as $server) {
                $environment = $server->targetable;
                if (!$environment instanceof Environment) {
                    continue;
                }

                $project = $environment->targetable;
                if (!$project instanceof Project) {
                    continue;
                }

                if (!$project->deploy_path) {
                    $project->deploy_path = $server->path;
                    $project->save();
                }
            }
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('deploy_path');
        });
    }
}
