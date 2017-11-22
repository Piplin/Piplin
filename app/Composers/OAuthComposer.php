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
use McCool\LaravelAutoPresenter\Facades\AutoPresenter;
use Piplin\Models\Provider;

/**
 * The composer for the OAuth.
 */
class OAuthComposer
{
    /**
     * Sets the logged in user into a view variable.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('provider_count', Provider::count());
        $view->with('providers', Provider::all());
    }
}
