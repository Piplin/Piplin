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
use Piplin\Http\Requests\StoreEnvironmentRequest;
use Piplin\Models\Command;
use Piplin\Models\Environment;
use Piplin\Models\EnvironmentLink;
use Piplin\Models\Link;
use Piplin\Models\Project;
use Piplin\Models\DeployPlan;

/**
 * Environment management controller.
 */
class EnvironmentController extends Controller
{
    /**
     * Display a listing of before/after commands for the supplied stage.
     *
     * @param DeployPlan     $project
     * @param Environment $environment
     * @param string      $tab
     *
     * @return Response
     */
    public function show(DeployPlan $deployPlan, Environment $environment, $tab = '')
    {
        $project = $deployPlan->project;

        $optional = $project->commands->filter(function (Command $command) {
            return $command->optional;
        });

        $breadcrumb = [
            ['url' => route('projects', ['id' => $project->id, 'tab' => 'environments']), 'label' => $project->name],
        ];
        $data = [
            'title'           => $environment->name,
            'breadcrumb'      => $breadcrumb,
            'project'         => $project,
            'deployPlan'      => $deployPlan,
            'targetable_type' => get_class($environment),
            'targetable_id'   => $environment->id,
            'environments'    => $deployPlan->environments,
            'targetable'      => $environment,
            'branches'        => $project->branches(),
            'tags'            => $project->tags()->reverse(),
            'optional'        => $optional,
            'tab'             => $tab,
        ];

        $links = [
            [
                'id'   => EnvironmentLink::MANUAL,
                'name' => trans('environments.link_manual'),
            ],
            [
                'id'   => EnvironmentLink::AUTOMATIC,
                'name' => trans('environments.link_auto'),
            ],
        ];
        if ($tab === 'deployments') {
            $data['deployments'] = $environment->tasks()->paginate(15);
        } elseif ($tab === 'links') {
            $data['links']            = $links;
            $data['environmentLinks'] = $environment->oppositePivot;
        } else {
            $data['servers']  = $environment->servers;
            $data['cabinets'] = $environment->cabinets->toJson();
        }

        return view('dashboard.environments.show', $data);
    }

    /**
     * Store a newly created environment in storage.
     *
     * @param StoreEnvironmentRequest $request
     *
     * @return Response
     */
    public function store(StoreEnvironmentRequest $request)
    {
        $fields = $request->only(
            'name',
            'description',
            'default_on',
            'add_commands',
            'targetable_type',
            'targetable_id'
        );

        $targetable_type = array_pull($fields, 'targetable_type');
        $targetable_id   = array_pull($fields, 'targetable_id');

        $add_commands = false;
        if (isset($fields['add_commands'])) {
            $add_commands = $fields['add_commands'];
            unset($fields['add_commands']);
        }

        $target = $targetable_type::findOrFail($targetable_id);

        $environment = $target->environments()->create($fields);

        // Add the environment to the existing commands
        if ($add_commands) {
            foreach ($environment->targetable->commands as $command) {
                $command->environments()->attach($environment->id);
            }
        }

        return $environment;
    }

    /**
     * Update the specified environment in storage.
     *
     * @param Environment             $environment
     * @param StoreEnvironmentRequest $request
     *
     * @return Response
     */
    public function update(Environment $environment, StoreEnvironmentRequest $request)
    {
        $environment->update($request->only(
            'name',
            'description',
            'default_on'
        ));

        return $environment;
    }

    /**
     * Re-generates the order for the supplied environments.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function reorder(Request $request)
    {
        $order = 0;

        foreach ($request->get('environments') as $environment_id) {
            $environment = Environment::findOrFail($environment_id);
            $environment->update([
                'order' => $order,
            ]);

            $order++;
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Remove the specified environment from storage.
     *
     * @param  Environment $environment
     * @return Response
     */
    public function destroy(Environment $environment)
    {
        $environment->delete();

        return [
            'success' => true,
        ];
    }
}
