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

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * View composer for the header bar.
 */
class ThemeComposer
{
    /**
     * Generates the pending and deploying projects for the view.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $theme = config('fixhub.theme');
        $user  = Auth::user();

        $language = config('app.locale');
        $dashboard = config('fixhub.dashboard');

        if ($user) {
            if (!empty($user->skin)) {
                $theme = $user->skin;
            }
            if (!empty($user->language)) {
                $language = $user->language;
            }
            if (!empty($user->dashboard)) {
                $dashboard = $user->dashboard;
            }
        }

        $view->with('theme', $theme);
        $view->with('language', $language);
        $view->with('dashboard', $dashboard);
    }
}
