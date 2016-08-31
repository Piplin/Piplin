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

use Illuminate\Support\ServiceProvider;

/**
 * The application service provider.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array Additional service providers to register for the environment.
     */
    private $providers = [
        'production' => [
            'GrahamCampbell\HTMLMin\HTMLMinServiceProvider',
        ],
        'local' => [
            'Clockwork\Support\Laravel\ClockworkServiceProvider',
            'Themsaid\Langman\LangmanServiceProvider',
        ],
    ];

    /**
     * @var array Additional web middleware to register for the environment.
     */
    private $middleware = [
        'production' => [
            'GrahamCampbell\HTMLMin\Http\Middleware\MinifyMiddleware',
        ],
        'local' => [
            'Clockwork\Support\Laravel\ClockworkMiddleware',
        ],
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register service providers and middleware dependent upon the enviroment.
     *
     * @return void
     */
    public function register()
    {
        $env = 'production';
        if ($this->app->environment('local')) {
            $env = 'local';
        }

        $this->registerAdditionalProviders($this->providers[$env]);
        $this->registerAdditionalMiddleware($this->middleware[$env]);
    }

    /**
     * Register additional service providers.
     *
     * @param  array $providers
     * @return void
     */
    private function registerAdditionalProviders(array $providers)
    {
        foreach ($providers as $provider) {
            if (class_exists($provider, true)) {
                $this->app->register($provider);
            }
        }
    }

    /**
     * Register additional middleware.
     *
     * @param  array $middlewares
     * @return void
     */
    private function registerAdditionalMiddleware(array $middlewares)
    {
        foreach ($middlewares as $middleware) {
            if (class_exists($middleware, true)) {
                $this->app->router->pushMiddlewareToGroup('web', $middleware);
            }
        }
    }
}
