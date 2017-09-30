<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Observers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Fixhub\Bus\Events\ModelChangedEvent;
use Fixhub\Bus\Jobs\SetupDeploymentJob;
use Fixhub\Models\Deployment;

/**
 * Event observer for Deployment model.
 */
class DeploymentObserver
{
    use DispatchesJobs;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var array
     */
    private $optional = [];

    /**
     * @var array
     */
    private $environments = [];

    /**
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Called when the model is being created.
     *
     * @param Deployment $deployment
     */
    public function creating(Deployment $deployment)
    {
        if ($deployment->optional) {
            $this->optional =$deployment->optional;
            unset($deployment->optional);
        }

        if ($deployment->environments) {
            $this->environments =$deployment->environments;
            unset($deployment->environments);
        }
    }

    /**
     * Called when the model is created.
     *
     * @param Deployment $deployment
     */
    public function created(Deployment $deployment)
    {
        $this->dispatch(new SetupDeploymentJob(
            $deployment,
            $this->environments,
            $this->optional
        ));
    }

    /**
     * Called when the model is saved.
     *
     * @param Deployment $deployment
     */
    public function saved(Deployment $deployment)
    {
        $this->dispatcher->dispatch(new ModelChangedEvent($deployment, 'deployment'));
    }
}
