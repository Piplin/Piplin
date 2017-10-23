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

use Fixhub\Models\ServerLog;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Fixhub\Models\Project;
use Fixhub\Models\ProjectGroup;
use Fixhub\Models\Deployment;
use Fixhub\Models\Hook;
use Fixhub\Models\ConfigFile;
use Fixhub\Models\SharedFile;
use Fixhub\Models\Variable;
use Fixhub\Models\Environment;
use Fixhub\Models\Server;

/**
 * The route service provider.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Fixhub\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Route::pattern('id', '[0-9]+');
        Route::pattern('step', '(clone|install|activate|purge)');

        Route::model('group', ProjectGroup::class);
        Route::model('project', Project::class);
        Route::model('deployment', Deployment::class);
        Route::model('hook', Hook::class);
        Route::model('server', Server::class);

        Route::model('environment', Environment::class);
        Route::model('command', Environment::class);
        Route::model('variable', Variable::class);
        Route::model('config_file', ConfigFile::class);
        Route::model('shared_file', SharedFile::class);
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();

        $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group(['namespace' => $this->namespace, 'middleware' => 'web'], function () {
            foreach (glob(base_path('routes') . '/web/*.php') as $file) {
                require $file;
            }
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group(['namespace' => $this->namespace, 'middleware' => 'api'], function () {
            foreach (glob(base_path('routes') . '/api/*.php') as $file) {
                require $file;
            }
        });
    }
}
