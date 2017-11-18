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

use Illuminate\Http\Request;
use Carbon\Carbon;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Models\Deployment;
use Fixhub\Models\Plan;
use Fixhub\Models\Project;

/**
 * The controller of plans.
 */
class PlanController extends Controller
{
    /**
     * The details of an individual plan.
     *
     * @param Plan   $plan
     * @param string $tab
     *
     * @return View
     */
    public function show(Plan $plan, $tab = '')
    {
        $project = $plan->project;
        $data = [
            'plan'            => $plan,
            'project'         => $project,
            'targetable_type' => 'Fixhub\\Models\\Plan',
            'targetable_id'   => $plan->id,
            'tags'            => $project->tags()->reverse(),
            'branches'        => $project->branches(),
            'environments'    => [],
            'optional'        => [],
            'deployments'     => $this->getLatest($plan),
            'tab'             => $tab,
            'servers'         => $plan->servers,
        ];

        return view('dashboard.plans.show', $data);
    }

    /**
     * Gets the latest deployments for a project.
     *
     * @param  Plan  $plan
     * @param  int   $paginate
     * @return array
     */
    private function getLatest(Plan $plan, $paginate = 15)
    {
        return Deployment::where('targetable_type', get_class($plan))
                           ->where('targetable_id', $plan->id)
                           ->with('user')
                           ->whereNotNull('started_at')
                           ->orderBy('started_at', 'DESC')
                           ->paginate($paginate);
    }
}
