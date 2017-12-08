<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Observers;

use Illuminate\Contracts\Events\Dispatcher;
use Piplin\Bus\Events\ModelChangedEvent;
use Piplin\Models\Task;

/**
 * Event observer for task model.
 */
class TaskObserver
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
     * @param Task $deployment
     */
    public function saved(Task $deployment)
    {
        $this->dispatcher->dispatch(new ModelChangedEvent($deployment, 'deployment'));
    }
}
