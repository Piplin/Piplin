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
use Piplin\Models\BuildPlan;
use Piplin\Models\Project;

/**
 * The controller of build plans.
 */
class BuildController extends Controller
{
    /**
     * The details of an individual plan.
     *
     * @param BuildPlan $buildPlan
     * @param string    $tab
     *
     * @return View
     */
    public function show(BuildPlan $buildPlan, $tab = '')
    {
        $project = $buildPlan->project;
        $data    = [
            'buildPlan'       => $buildPlan,
            'project'         => $project,
            'targetable_type' => get_class($buildPlan),
            'targetable_id'   => $buildPlan->id,
            'tags'            => $project->tags()->reverse(),
            'branches'        => $project->branches(),
            'environments'    => [],
            'optional'        => [],
            'tasks'           => $this->getLatest($buildPlan),
            'tab'             => $tab,
            'servers'         => $buildPlan->servers,
            'patterns'        => $buildPlan->patterns,
            'breadcrumb'      => [
                ['url' => route('projects', ['id' => $project->id]), 'label' => $project->name],
                ['url' => route('builds', ['id' => $buildPlan->id]), 'label' => trans('plans.label')],
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
     * @param  BuildPlan  $buildPlan
     * @param  int        $paginate
     * @return array
     */
    private function getLatest(BuildPlan $buildPlan, $paginate = 15)
    {
        return Task::where('targetable_type', get_class($buildPlan))
                           ->where('targetable_id', $buildPlan->id)
                           ->with('user')
                           ->whereNotNull('started_at')
                           ->orderBy('started_at', 'DESC')
                           ->paginate($paginate);
    }
}
