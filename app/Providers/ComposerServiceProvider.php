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

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider;
use Piplin\Composers\AdminComposer;
use Piplin\Composers\AppComposer;
use Piplin\Composers\CurrentUserComposer;
use Piplin\Composers\DashboardComposer;
use Piplin\Composers\HeaderComposer;
use Piplin\Composers\OAuthComposer;
use Piplin\Composers\ProjectComposer;
use Piplin\Composers\ProjectSummaryComposer;
use Piplin\Composers\ThemeComposer;
use Piplin\Composers\TimelineComposer;
use Piplin\Composers\UpdateComposer;

/**
 * The composer service provider.
 */
class ComposerServiceProvider extends ServiceProvider
{
    public $composers = [
        AdminComposer::class       => [
            'admin.index',
            'admin.*.index',
            'admin.templates.show',
            'admin.groups.show',
            'admin.cabinets.show',
        ],
        AppComposer::class            => '*',
        CurrentUserComposer::class    => '*',
        DashboardComposer::class      => ['dashboard._partials.shortcut'],
        HeaderComposer::class         => ['_partials.nav'],
        ProjectComposer::class        => ['dashboard._partials.sidebar', 'dashboard.projects'],
        ProjectSummaryComposer::class => ['dashboard.projects._partials.summary'],
        ThemeComposer::class          => ['layouts.dashboard', 'profile.index'],
        TimelineComposer::class       => ['dashboard.timeline'],
        UpdateComposer::class         => ['admin._partials.update'],
        OAuthComposer::class          => ['auth.login'],
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
