<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Providers;

use Fixhub\Services\Update\LatestRelease;
use Fixhub\Services\Update\LatestReleaseInterface;
use Illuminate\Support\ServiceProvider;

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

        $this->app->singleton('fixhub.update-check', function ($app) {
            $cache = $app['cache.store'];

            return new LatestRelease($cache);
        });

        $this->app->alias('fixhub.update-check', LatestReleaseInterface::class);
    }
}
