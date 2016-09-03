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

use Fixhub\Composers\AppComposer;
use Fixhub\Composers\CurrentUserComposer;
use Fixhub\Composers\DashboardComposer;
use Fixhub\Composers\DeploymentComposer;
use Fixhub\Composers\HeaderComposer;
use Fixhub\Composers\SidebarComposer;
use Fixhub\Composers\ThemeComposer;
use Fixhub\Composers\VersionComposer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider;

/**
 * The composer service provider.
 */
class ComposerServiceProvider extends ServiceProvider
{
    public $composers = [
        AppComposer::class         => '*',
        CurrentUserComposer::class => '*',
        DashboardComposer::class   => ['dashboard.index'],
        DeploymentComposer::class  => ['projects.show'],
        HeaderComposer::class      => ['dashboard._partials.nav'],
        SidebarComposer::class     => ['dashboard._partials.sidebar'],
        ThemeComposer::class       => ['layouts.dashboard', 'profile.index'],
        VersionComposer::class     => ['dashboard._partials.update'],
    ];

    /**
     * Bootstrap the application services.
     *
     * @param  \Illuminate\Contracts\View\Factory $factory
     * @return void
     */
    public function boot(Factory $factory)
    {
        foreach ($this->composers as $composer => $views) {
            $factory->composer($views, $composer);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
