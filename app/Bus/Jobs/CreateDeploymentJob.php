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

use Fixhub\Models\Server;
use Fixhub\Models\Environment;
use Fixhub\Models\Cabinet;
use Fixhub\Models\Deployment;
use Fixhub\Models\Project;
use Fixhub\Services\Scripts\Runner as Process;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\Queue;

/**
 * Tests if a server can successfully be SSHed into.
 */
class CreateDeploymentJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
    * @var int
    */
    public $timeout = 0;

    /**
    * @var Project
    */
    public $project;

    private $fields;

    /**
     * Create a new command instance.
     *
     * @param Server $server
     *
     * @return TestServerConnectionJob
     */
    public function __construct(Project $project, array $fields = [])
    {
        $this->project = $project;
        $this->fields = $fields;
    }

    /**
     * Overwrite the queue method to push to a different queue.
     *
     * @param  Queue               $queue
     * @param  CreateDeploymentJob $command
     */
    public function queue(Queue $queue, $command)
    {
        $queue->pushOn('fixhub-high', $command);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $optional = array_pull($this->fields, 'optional');
        $environments = array_pull($this->fields, 'environments');

        $deployment = Deployment::create($this->fields);

        $this->dispatch(new SetupDeploymentJob(
            $deployment,
            $environments,
            $optional
        ));
    }
}
