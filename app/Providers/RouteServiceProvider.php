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

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Piplin\Models\Artifact;
use Piplin\Models\Command;
use Piplin\Models\ConfigFile;
use Piplin\Models\Task;
use Piplin\Models\Environment;
use Piplin\Models\Hook;
use Piplin\Models\BuildPlan;
use Piplin\Models\DeployPlan;
use Piplin\Models\Project;
use Piplin\Models\ProjectGroup;
use Piplin\Models\Provider;
use Piplin\Models\Release;
use Piplin\Models\Server;
use Piplin\Models\ServerLog;
use Piplin\Models\SharedFile;
use Piplin\Models\Tip;
use Piplin\Models\User;
use Piplin\Models\Variable;
use Piplin\Models\Pattern;

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
    protected $namespace = 'Piplin\Http\Controllers';

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

        Route::model('artifact', Artifact::class);
        Route::model('group', ProjectGroup::class);
        Route::model('project', Project::class);
        Route::model('build', BuildPlan::class);
        Route::model('deployment', DeployPlan::class);
        Route::model('task', Task::class);
        Route::model('hook', Hook::class);
        Route::model('release', Release::class);
        Route::model('server', Server::class);
        Route::model('provider', Provider::class);
        Route::model('user', User::class);
        Route::model('pattern', Pattern::class);

        Route::model('environment', Environment::class);
        Route::model('command', Command::class);
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
