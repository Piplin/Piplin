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

use Illuminate\Foundation\Bus\DispatchesJobs;
use Piplin\Models\Task;

/**
 * Deploys a draft.
 */
class DeployDraftJob extends Job
{
    use DispatchesJobs;

    /**
     * @var Task
     */
    private $deployment;

    /**
     * Create a new command instance.
     *
     * @param Task $deployment
     * @param array      $environmentIds
     * @param array      $optional
     *
     * @return void
     */
    public function __construct(Task $deployment)
    {
        $this->deployment     = $deployment;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->deployment->isDraft()) {
            return;
        }

        $this->deployment->status = Task::PENDING;
        $this->deployment->save();

        $this->dispatch(new RunTaskJob($this->deployment));
    }
}
