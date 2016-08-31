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
use Fixhub\Models\Template;
use Illuminate\Support\Facades\Response;

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
        return view('dashboard.index', [
            'title'     => trans('dashboard.title'),
            'latest'    => $this->buildTimelineData(),
        ]);
    }

    /**
     * Returns the timeline.
     *
     * @return View
     */
    public function timeline()
    {
        return view('dashboard.timeline', [
            'latest' => $this->buildTimelineData(),
        ]);
    }

    /**
     * Builds the data for the timline.
     *
     * @return array
     */
    private function buildTimelineData()
    {
        $raw_sql = 'project_id IN (SELECT id FROM projects WHERE deleted_at IS NULL)';

        $deployments = Deployment::whereRaw($raw_sql)
                           ->whereNotNull('started_at')
                           ->with('project')
                           ->take(10)
                           ->orderBy('started_at', 'DESC')
                           ->get();

        $deploys_by_date = [];
        foreach ($deployments as $deployment) {
            $date = $deployment->started_at->format('Y-m-d');

            if (!isset($deploys_by_date[$date])) {
                $deploys_by_date[$date] = [];
            }

            $deploys_by_date[$date][] = $deployment;
        }

        return $deploys_by_date;
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
