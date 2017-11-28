<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Composers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * View composer for the header bar.
 */
class ThemeComposer
{
    /**
     * Generates the pending and running projects for the view.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $theme = config('piplin.theme');
        $user  = Auth::user();

        $language  = config('app.locale');
        $dashboard = config('piplin.dashboard');

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
