<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Dashboard;

use Fixhub\Http\Controllers\Controller;
use Fixhub\Models\Deployment;
use Fixhub\Models\ProjectGroup;
use Fixhub\Models\Project;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

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
            $dashboard = config('fixhub.dashboard');
        }

        $method = $dashboard == 'deployments' ? 'deployments' : 'projects';
        
        return $this->{$method}();
    }

    /**
     * Returns the deployments.
     *
     * @return View
     */
    public function deployments()
    {
        return view('dashboard.index');
    }

    /**
     * Returns the projects.
     *
     * @return View
     */
    public function projects()
    {
        return view('dashboard.projects');
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

    /**
     * Generates an XML file for CCTray.
     *
     * @return Response
     */
    public function cctray()
    {
        $projects = Project::orderBy('name')
                    ->get();

        foreach ($projects as $project) {
            $project->latest_deployment = $project->deployments->first();
        }

        return Response::view('dashboard.cctray', [
            'projects' => $projects,
        ])->header('Content-Type', 'application/xml');
    }
}
