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
use Piplin\Bus\Jobs\Repository\UpdateGitMirrorJob;
use Piplin\Models\Project;

/**
 * Trigger git updating job.
 */
class TriggerGitUpdateJob extends Job implements ShouldQueue
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
     * Create a new command instance.
     *
     * @param Project $project
     *
     * @return TriggerGitJob
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
        $this->dispatch(new UpdateGitMirrorJob($this->project));
    }
}
