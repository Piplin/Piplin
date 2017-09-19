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

use Fixhub\Models\Deployment;
use Illuminate\Support\Facades\Cache;

/**
 * A class to handle caching the abort request.
 */
class AbortDeployment extends Job
{
    /**
    * @var Deployment
    */
    private $deployment;

    const CACHE_KEY_PREFIX = 'fixhub:cancel-deploy:';

    /**
     * Create a new job instance.
     *
     * @param Deployment $deployment
     */
    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Cache::put(self::CACHE_KEY_PREFIX . $this->deployment->id, time(), 3600);
    }
}
