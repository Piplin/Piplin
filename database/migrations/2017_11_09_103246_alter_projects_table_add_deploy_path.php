<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Fixhub\Models\Server;
use Fixhub\Models\Environment;
use Fixhub\Models\Project;

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

                if(!$project->deploy_path) {
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
