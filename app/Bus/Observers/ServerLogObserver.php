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
use Fixhub\Bus\Events\ServerLogChangedEvent;
use Fixhub\Bus\Events\ServerOutputChangedEvent;
use Fixhub\Models\ServerLog;

/**
 * Event observer for ServerLog model.
 */
class ServerLogObserver
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
     * Called when the model is updated.
     *
     * @param ServerLog $log
     */
    public function updated(ServerLog $log)
    {
        $this->dispatcher->dispatch(new ServerLogChangedEvent($log));

        if ($log->isDirty('output')) {
            $this->dispatcher->dispatch(new ServerOutputChangedEvent($log));
        }
    }
}
