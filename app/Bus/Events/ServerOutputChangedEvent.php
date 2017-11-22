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
use Piplin\Models\ServerLog;

/**
 * Event which fires when the server log content has changed.
 */
class ServerOutputChangedEvent extends Event implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @var int
     */
    public $log_id;

    /**
     * ServerOutputChanged constructor.
     *
     * @param ServerLog $log
     */
    public function __construct(ServerLog $log)
    {
        $this->log_id = $log->id;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['serverlog-' . $this->log_id];
    }
}
