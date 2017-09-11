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

use Carbon\Carbon;
use Fixhub\Bus\Jobs\DeployProject;
use Fixhub\Models\Command as Stage;
use Fixhub\Models\Deployment;
use Fixhub\Models\DeployStep;
use Fixhub\Models\Project;
use Fixhub\Models\ServerLog;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;

/**
 * Generates the required database entries to approve a deployment.
 */
class ApproveDeployment extends Job
{
    use DispatchesJobs;

    private $deployment;
    private $optional;

    /**
     * Create a new command instance.
     *
     * @param Deployment      $deployment
     * @param array $optional
     */
    public function __construct(Deployment $deployment, array $optional = [])
    {
        $this->deployment = $deployment;
        $this->optional   = $optional;
    }

    /**
     * Execute the command.
     */
    public function handle()
    {
        $this->dispatch(new DeployProject($this->deployment));
        /*
        $this->dispatch(new QueueDeployment(
            $this->deployment,
            $this->optional
        ));
        */
    }
}
