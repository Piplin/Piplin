<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Providers;

use Illuminate\Support\ServiceProvider;
use Piplin\Services\Update\LatestRelease;
use Piplin\Services\Update\LatestReleaseInterface;

/**
 * Service provider to register the LatestRelease class as a singleton.
 **/
class UpdateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Define a constant for the application version
        if (!defined('APP_VERSION')) {
            define('APP_VERSION', trim(file_get_contents(app_path('../VERSION'))));
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LatestReleaseInterface::class, LatestRelease::class);

        $this->app->singleton('piplin.update-check', function ($app) {
            $cache = $app['cache.store'];

            return new LatestRelease($cache);
        });

        $this->app->alias('piplin.update-check', LatestReleaseInterface::class);
    }
}
