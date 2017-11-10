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

use Fixhub\Composers\AdminComposer;
use Fixhub\Composers\AppComposer;
use Fixhub\Composers\CurrentUserComposer;
use Fixhub\Composers\DashboardComposer;
use Fixhub\Composers\DeploymentComposer;
use Fixhub\Composers\HeaderComposer;
use Fixhub\Composers\ProjectComposer;
use Fixhub\Composers\SidebarComposer;
use Fixhub\Composers\ThemeComposer;
use Fixhub\Composers\TimelineComposer;
use Fixhub\Composers\UpdateComposer;
use Fixhub\Composers\OAuthComposer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider;

/**
 * The composer service provider.
 */
class ComposerServiceProvider extends ServiceProvider
{
    public $composers = [
        AdminComposer::class       => [
            'admin.*.index',
            'admin.templates.show',
            'admin.groups.show',
            'admin.cabinets.show',
        ],
        AppComposer::class         => '*',
        CurrentUserComposer::class => '*',
        DashboardComposer::class   => ['dashboard._partials.shortcut'],
        DeploymentComposer::class  => ['dashboard.projects.show'],
        HeaderComposer::class      => ['_partials.nav'],
        SidebarComposer::class     => ['dashboard._partials.sidebar'],
        ProjectComposer::class     => ['dashboard._partials.sidebar', 'dashboard.projects'],
        ThemeComposer::class       => ['layouts.dashboard', 'profile.index'],
        TimelineComposer::class    => ['dashboard.timeline'],
        UpdateComposer::class      => ['admin._partials.update'],
        OAuthComposer::class       => ['auth.login'],
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
