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
use Fixhub\Models\NotifySlack;
use Httpful\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sends notification to slack.
 */
class NotifySlackJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $payload;
    private $notify_slack;
    private $timeout;

    /**
     * Create a new command instance.
     *
     * @param NotifySlack $notify_slack
     * @param array       $payload
     * @param int         $timeout
     */
    public function __construct(NotifySlack $notify_slack, array $payload, $timeout = 60)
    {
        $this->notify_slack = $notify_slack;
        $this->payload      = $payload;
        $this->timeout      = $timeout;
    }

    /**
     * Execute the command.
     */
    public function handle()
    {
        $payload = [
            'channel'  => $this->notify_slack->channel,
            'username' => 'Fixhub',
        ];

        if (!empty($this->notify_slack->icon)) {
            $icon_field = 'icon_url';
            if (preg_match('/:(.*):/', $this->notify_slack->icon)) {
                $icon_field = 'icon_emoji';
            }

            $payload[$icon_field] = $this->notify_slack->icon;
        }

        $payload = array_merge($payload, $this->payload);

        if (isset($payload['attachments'])) {
            $expire_at = Carbon::createFromTimestamp($payload['attachments'][0]['ts'])->addMinutes($this->timeout);

            if (Carbon::now()->gt($expire_at)) {
                return;
            }
        }

        Request::post($this->notify_slack->webhook)
               ->sendsJson()
               ->body($payload)
               ->send();
    }
}
