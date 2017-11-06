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
        $data = $this->buildTimelineData();
        return view('dashboard.index', [
            'latest'          => $data[0],
            'deployments_raw' => $data[1],
        ]);
    }

    /**
     * Returns the projects.
     *
     * @return View
     */
    public function projects()
    {
        return view('dashboard.projects', []);
    }

    /**
     * Returns the timeline.
     *
     * @return View
     */
    public function timeline()
    {
        $data = $this->buildTimelineData();
        return view('dashboard.timeline', [
            'latest'          => $data[0],
            'deployments_raw' => $data[1],
        ]);
    }

    /**
     * Builds the data for the timline.
     *
     * @return array
     */
    private function buildTimelineData()
    {
        $user = Auth::user();
        $deployments = Deployment::whereNotNull('started_at');

        if (!$user->is_admin) {

            $projectIds = array_merge($user->personal_projects->pluck('id')->toArray(), $user->authorized_projects->pluck('id')->toArray());

            $deployments = $deployments->whereIn('project_id', $projectIds);
        }

        $deployments = $deployments->orderBy('started_at', 'DESC')
                           ->paginate(10);

        $deploys_by_date = [];
        foreach ($deployments as $deployment) {
            $date = $deployment->started_at->format('Y-m-d');

            if (!isset($deploys_by_date[$date])) {
                $deploys_by_date[$date] = [];
            }

            $deploys_by_date[$date][] = $deployment;
        }

        return [$deploys_by_date, $deployments];
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
