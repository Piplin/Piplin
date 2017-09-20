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

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Whoops\Handler\HandlerInterface as WhoopsHandlerInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

/**
 * Service provider to provide the Whoops exception handle.
 */
class WhoopsServiceProvider extends ServiceProvider
{
    /**
     * Defer loading until actually needed.
     *
     * @var bool
     */
    public $defer = true;

    /**
     * Register the application services.
     */
    public function register()
    {
        if (!$this->useWhoops()) {
            return false;
        }

        $this->app->bind(Whoops::class, function (Application $app) {
            $whoops = new Whoops();

            if ($app->make(Request::class)->expectsJson()) {
                $whoops->pushHandler(new JsonResponseHandler());
            } else {
                $whoops->pushHandler(new PrettyPageHandler());
            }

            return $whoops;
        });

        return true;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        if (!$this->useWhoops()) {
            return [];
        }

        return [Whoops::class];
    }

    /**
     * Determines whether or not whoops should be bound to the service container.
     *
     * @return bool
     */
    private function useWhoops()
    {
        // Only register if debugging is enabled and it is installed, i.e. on dev
        if (!$this->app->make('config')->get('app.debug', false) ||
            !interface_exists(WhoopsHandlerInterface::class, true)
        ) {
            return false;
        }

        return true;
    }
}