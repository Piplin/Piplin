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
use Fixhub\Bus\Events\ModelChangedEvent;
use Fixhub\Bus\Jobs\SetupDeploymentJob;
use Fixhub\Models\Deployment;

/**
 * Event observer for Deployment model.
 */
class DeploymentObserver
{

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
