<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Piplin\Http\Controllers\Controller;

/**
 * The dashboard controller.
 */
class DashboardController extends Controller
{
    /**
     * The main page of the dashboard.
     *
     * @return View
     */
    public function index()
    {
        $dashboard = Auth::user()->dashboard;

        if (empty($dashboard)) {
            $dashboard = config('piplin.dashboard');
        }

        //$method = $dashboard === 'projects' ? 'projects' : 'tasks';
        $method = 'projects';

        return $this->{$method}();
    }

    /**
     * Returns the tasks.
     *
     * @return View
     */
    public function activities()
    {
        return view('dashboard.activities');
    }

    /**
     * Returns the projects.
     *
     * @return View
     */
    public function projects()
    {
        return view('dashboard.projects')
                    ->with('title', trans('users.dashboard.projects'));
    }

    /**
     * Returns the timeline.
     *
     * @return View
     */
    public function timeline()
    {
        return view('dashboard.timeline');
    }
}
