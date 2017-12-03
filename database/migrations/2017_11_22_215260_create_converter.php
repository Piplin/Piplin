<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Piplin\Models\Environment;
use Piplin\Models\Project;
use Piplin\Models\Variable;
use Piplin\Models\Command;
use Piplin\Models\ConfigFile;
use Piplin\Models\SharedFile;
use Piplin\Models\ProjectGroup;
use Piplin\Models\User;
use Piplin\Models\Cabinet;
use Piplin\Models\Server;
use Piplin\Models\BuildPlan;
use Piplin\Models\DeployPlan;
use Piplin\Models\Task;

class CreateConverter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $environments = Environment::withTrashed()->get();
        $this->convert($environments);

        $variables = Variable::withTrashed()->get();
        $this->convert($variables);

        $commands = Command::withTrashed()->get();
        $this->convert($commands);

        $configFiles = ConfigFile::withTrashed()->get();
        $this->convert($configFiles);

        $sharedFiles = SharedFile::withTrashed()->get();
        $this->convert($sharedFiles);

        $servers = Server::withTrashed()->get();
        $this->convert($servers);

        $projects = Project::withTrashed()->get();
        $this->convert($projects);

        $tasks = Task::withTrashed()->get();
        foreach ($tasks as $task) {
            $project = Project::find($task->project_id);
            $deployPlan = DeployPlan::where('project_id', $project->id)->first();
            $task->targetable_type = DeployPlan::class;
            $task->targetable_id = $deployPlan->id;
            $task->save();
        }
    }

    private function convert($items)
    {
        foreach ($items as $item) {
            if (Str::endsWith($item->targetable_type, 'ProjectGroup')) {
                $item->targetable_type = ProjectGroup::class;
            } elseif (Str::endsWith($item->targetable_type, 'User')) {
                $item->targetable_type = User::class;
            } elseif (Str::endsWith($item->targetable_type, 'Cabinet')) {
                $item->targetable_type = Cabinet::class;
            } elseif (Str::endsWith($item->targetable_type, 'Environment')) {
                $item->targetable_type = Environment::class;
            } else {
                $item->targetable_type = Project::class;
            }
            $item->save();

            if ($item->targetable_type && $item->targetable_type == Project::class) {
                $project = Project::find($item->targetable_id);
                if (!$project) {
                    continue;
                }
                if (!$project->deployPlan) {
                    $project->deployPlan = DeployPlan::create([
                        'name'       => $project->name,
                        'project_id' => $project->id,
                    ]);
                }
                $item->targetable_type = DeployPlan::class;
                $item->targetable_id = $project->deployPlan->id;
                $item->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
