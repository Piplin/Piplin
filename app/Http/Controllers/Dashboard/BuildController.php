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

use Illuminate\Http\Request;
use Piplin\Http\Controllers\Controller;
use Piplin\Models\Command;
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
     * @param Request   $request
     * @param BuildPlan $buildPlan
     * @param string    $tab
     *
     * @return View
     */
    public function show(Request $request, BuildPlan $buildPlan, $tab = '')
    {
        $project = $buildPlan->project;

        $optional = $buildPlan->commands->filter(function (Command $command) {
            return $command->optional;
        });

        $data    = [
            'buildPlan'       => $buildPlan,
            'project'         => $project,
            'targetable_type' => get_class($buildPlan),
            'targetable_id'   => $buildPlan->id,
            'tags'            => $project->tags()->reverse(),
            'branches'        => $project->branches(),
            'environments'    => [],
            'optional'        => $optional,
            'releases'        => [],
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
        } elseif ($tab === 'releases') {
            $data['title'] = trans('releases.label');
            $data['releases'] = $project->releases;
        } else {
            // Don't trigger dialog if page changed
            if ($request->get('page')) {
                $data['tab'] = null;
            }
            $data['title'] = trans('plans.builds');
        }

        return view('dashboard.builds.show', $data);
    }

    /**
     * Gets the latest deployments for a project.
     *
     * @param  BuildPlan $buildPlan
     * @param  int       $paginate
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
