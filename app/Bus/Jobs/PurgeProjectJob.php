<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Jobs;

use Fixhub\Models\Project;
use Fixhub\Services\Scripts\Runner as Process;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Purge project stuff.
 */
class PurgeProjectJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
    * @var int
    */
    public $timeout = 0;

    /**
    * @var Project
    */
    public $project;

    /**
     * Create a new command instance.
     *
     * @param Project $project
     *
     * @return PurgeProjectJob
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->project->variables()->forceDelete();
        $this->project->sharedFiles()->forceDelete();
        $this->project->hooks()->forceDelete();
        $this->project->members()->detach();

        foreach ($this->project->commands as $command) {
            $command->environments()->detach();
        }

        foreach ($this->project->environments as $environment) {
            $environment->commands()->detach();
            $environment->configFiles()->detach();

            foreach($environment->servers as $server) {
                $server->logs()->forceDelete();
                $server->forceDelete();
            }
        }

        foreach ($this->project->deployments as $deployment) {
            $deployment->steps()->forceDelete();
            $deployment->environments()->detach();
            $deployment->forceDelete();
        }

        $this->project->configFiles()->forceDelete();
        $this->project->environments()->forceDelete();
        $this->project->commands()->forceDelete();
    }

}