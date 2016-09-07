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

use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Request the urls.
 */
class RequestProjectCheckUrl extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    private $links;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($links)
    {
        $this->links = $links;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->links as $link) {
            $has_error = false;

            try {
                $response = Request::get($link->url)->send();

                $has_error = $response->hasErrors();
            } catch (ConnectionErrorException $error) {
                $has_error = true;
            }

            $link->last_status = $has_error;
            $link->save();

            if ($has_error) {
                foreach ($link->project->notifySlacks as $notifyslack) {
                    try {
                        $this->dispatch(new NotifySlackJob($notifyslack, $link->notifySlackPayload()));
                    } catch (\Exception $error) {
                        // Don't worry about this error
                    }
                }
            }
        }
    }
}
