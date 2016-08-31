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

use Fixhub\Models\Heartbeat;
use Illuminate\Queue\SerializesModels;

/**
 * Event class which is thrown when the heartbeat recovers.
 **/
class HeartbeatRecovered extends Event implements HasSlackPayloadInterface
{
    use SerializesModels;

    public $heartbeat;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Heartbeat $heartbeat)
    {
        $this->heartbeat = $heartbeat;
    }

    /**
     * Generates a slack payload for the heartbeat recovery.
     *
     * @return array
     */
    public function notifySlackPayload()
    {
        $message = trans('heartbeats.recovered_message', ['job' => $this->heartbeat->name]);
        $url     = route('projects', ['id' => $this->heartbeat->project_id]);

        $payload = [
            'attachments' => [
                [
                    'fallback' => $message,
                    'text'     => $message,
                    'color'    => 'good',
                    'fields'   => [
                        [
                            'title' => trans('notifySlacks.project'),
                            'value' => sprintf('<%s|%s>', $url, $this->heartbeat->project->name),
                            'short' => true,
                        ],
                    ],
                    'footer' => trans('app.name'),
                    'ts'     => time(),
                ],
            ],
        ];

        return $payload;
    }
}
