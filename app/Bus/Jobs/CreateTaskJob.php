<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Queue;
use Illuminate\Queue\SerializesModels;
use Piplin\Models\Task;
use Piplin\Models\Project;
use Piplin\Services\Scripts\Runner as Process;

/**
 * Create task job.
 */
class CreateTaskJob extends Job implements ShouldQueue
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

    /**
     * @var array
     */
    private $fields;

    /**
     * Create a new command instance.
     *
     * @param Project $project
     * @param array   $fields
     *
     * @return CreateTaskJob
     */
    public function __construct(Project $project, array $fields = [])
    {
        $this->project = $project;
        $this->fields  = $fields;
    }

    /**
     * Overwrite the queue method to push to a different queue.
     *
     * @param Queue         $queue
     * @param CreateTaskJob $command
     */
    public function queue(Queue $queue, $command)
    {
        $queue->pushOn('piplin-high', $command);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $optional     = array_pull($this->fields, 'optional');
        $environments = array_pull($this->fields, 'environments');

        $task = Task::create($this->fields);

        $this->dispatch(new SetupTaskJob(
            $task,
            $environments,
            $optional
        ));
    }
}
