<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

/**
 * Event which fires when the server status has changed.
 */
class ModelTrashed extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $model;
    private $channel;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($model, $channel)
    {
        $this->model = [
            'id'         => $model->id,
            'project_id' => $model->project_id,
        ];

        $this->channel = $channel;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [$this->channel];
    }
}
