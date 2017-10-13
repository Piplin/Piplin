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

use Fixhub\Bus\Events\Event;
use Fixhub\Models\Deployment;
use Fixhub\Models\Project;
use Illuminate\Queue\SerializesModels;

/**
 * Deploy finished event.
 */
class DeployFinishedEvent extends Event
{
    use SerializesModels;

    public $deployment;

    /**
     * Create a new event instance.
     *
     * @param Deployment $deployment
     */
    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }
}
