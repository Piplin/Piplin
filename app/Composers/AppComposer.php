<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Composers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\View\View;

/**
 * The composer for the app.
 */
class AppComposer
{
    /**
     * The illuminate config instance.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Create a new app composer instance.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     *
     * @return void
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Sets the config to view variables.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('app_name', $this->config->get('setting.app_name'));
        $view->with('app_url', $this->config->get('setting.app_url'));
        $view->with('app_about', $this->config->get('setting.app_about'));
        $view->with('app_locale', $this->config->get('setting.app_locale'));
    }
}
