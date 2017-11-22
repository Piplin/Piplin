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

use Carbon\Carbon;
use Illuminate\Http\Request;
use Piplin\Http\Controllers\Controller;
use Piplin\Models\Task;
use Piplin\Models\Plan;
use Piplin\Models\Project;

/**
 * The controller of build plans.
 */
class BuildController extends Controller
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
        $data    = [
            'plan'            => $plan,
            'project'         => $project,
            'targetable_type' => get_class($plan),
            'targetable_id'   => $plan->id,
            'tags'            => $project->tags()->reverse(),
            'branches'        => $project->branches(),
            'environments'    => [],
            'optional'        => [],
            'deployments'     => $this->getLatest($plan),
            'tab'             => $tab,
            'servers'         => $plan->servers,
            'patterns'        => $plan->patterns,
            'breadcrumb'      => [
                ['url' => route('projects', ['id' => $project->id]), 'label' => $project->name],
                ['url' => route('builds', ['id' => $plan->id]), 'label' => trans('plans.label')],
            ],
        ];

        if ($tab === 'commands') {
            $data['title'] = trans('plans.commands');
        } elseif ($tab === 'agents') {
            $data['title'] = trans('plans.agents');
        } elseif ($tab === 'patterns') {
            $data['title'] = trans('patterns.label');
        } else {
            $data['title'] = trans('plans.builds');
        }

        return view('dashboard.builds.show', $data);
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
        return Task::where('targetable_type', get_class($plan))
                           ->where('targetable_id', $plan->id)
                           ->with('user')
                           ->whereNotNull('started_at')
                           ->orderBy('started_at', 'DESC')
                           ->paginate($paginate);
    }
}
