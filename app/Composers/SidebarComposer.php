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

use Cache;
use Fixhub\Models\ProjectGroup;
use Fixhub\Models\Link;
use Fixhub\Models\Project;
use Fixhub\Models\Tip;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * View composer for the side bar.
 */
class SidebarComposer
{
    const CACHE_MINUTES = 10;

    /**
     * Generates the pending and deploying projects for the view.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->withLinks($this->getLinks());
        $view->withTip($this->getRandomTip());
    }

    /**
     * Gets a random tip.
     *
     * @return array
     */
    protected function getRandomTip()
    {
        $tips = Cache::remember('random_tip', self::CACHE_MINUTES, function () {
            return Tip::where('status', true)->orderBy('id', 'desc')->take(20)->get();
        });

        return ($tips && $tips->count() > 0) ? $tips->random() : null;
    }

    /**
     * Gets the links.
     *
     * @return array
     */
    protected function getLinks()
    {
        return Cache::remember('links', self::CACHE_MINUTES, function () {
            return Link::orderBy('order', 'asc')->take(10)->get();
        });
    }
}
