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
        $data = $this->buildTimelineData();
        return view('dashboard.index', [
            'latest'          => $data[0],
            'deployments_raw' => $data[1],
        ]);
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
        $deployments = Deployment::whereNotNull('started_at')
                           ->orderBy('started_at', 'DESC')
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
