<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use McCool\LaravelAutoPresenter\Facades\AutoPresenter;
use Piplin\Models\ServerLog;

/**
 * Event which fires when the server log status has changed.
 */
class ServerLogChangedEvent extends Event implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @var int
     */
    public $log_id;

    /**
     * @var null|string
     */
    public $output;

    /**
     * @var string|null
     */
    public $runtime;

    /**
     * @var int
     */
    public $status;

    /**
     * @var string|null
     */
    public $started_at;

    /**
     * @var string|null
     */
    public $finished_at;

    /**
     * Create a new event instance.
     *
     * @param ServerLog $log
     */
    public function __construct(ServerLog $log)
    {
        $this->status      = $log->status;
        $this->started_at  = $log->started_at ? $log->started_at->toDateTimeString() : null;
        $this->finished_at = $log->finished_at ? $log->finished_at->toDateTimeString() : null;
        $this->log_id      = $log->id;
        $this->output      = ((is_null($log->output) || !strlen($log->output)) ? null : '');
        $this->runtime     = ($log->runtime() === false ? null : AutoPresenter::decorate($log)->readable_runtime);
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['serverlog'];
    }
}
