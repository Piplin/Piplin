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

use Fixhub\Models\Setting as SettingModel;
use Fixhub\Services\Settings\Cache;
use Fixhub\Services\Settings\Repository;
use Exception;
use Illuminate\Support\ServiceProvider;

/**
 * This is the config service provider class.
 *
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $env = $this->app->environment();
        $repo = $this->app->make(Repository::class);
        $cache = $this->app->make(Cache::class);
        $loaded = $cache->load($env);

        $this->app->terminating(function () use ($repo, $cache) {
            if ($repo->stale()) {
                $cache->clear();
            }
        });

        try {
            if ($loaded === false) {
                $loaded = $repo->all();
                $cache->store($env, $loaded);
            }

            $settings = array_merge($this->app->config->get('setting'), $loaded);

            $this->app->config->set('setting', $settings);
        } catch (Exception $e) {
            //
        }

        if ($appUrl = $this->app->config->get('setting.app_url')) {
            $this->app->config->set('app.url', $appUrl);
        }

        if ($appLocale = $this->app->config->get('setting.app_locale')) {
            $this->app->config->set('app.locale', $appLocale);
            $this->app->translator->setLocale($appLocale);
        }

        if ($appTimezone = $this->app->config->get('setting.app_timezone')) {
            $this->app->config->set('fixhub.timezone', $appTimezone);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Cache::class, function ($app) {
            return new Cache($app->files, $app->bootstrapPath().'/fixhub');
        });

        $this->app->singleton(Repository::class, function () {
            return new Repository(new SettingModel());
        });
    }
}
