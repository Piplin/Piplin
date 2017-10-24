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

use Illuminate\Foundation\Bus\DispatchesJobs;
use Fixhub\Bus\Jobs\DeployProjectJob;
use Fixhub\Models\Deployment;

/**
 * Deploys an actual project.
 */
class DeployDraftProjectJob extends Job
{
    use DispatchesJobs;

    /**
     * @var Deployment
     */
    private $deployment;

    /**
     * Create a new command instance.
     *
     * @param Deployment $deployment
     * @param array $environmentIds
     * @param array $optional
     *
     * @return void
     */
    public function __construct(Deployment $deployment)
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
        $this->dispatch(new DeployProjectJob($this->deployment));
    }
}
