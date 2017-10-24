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
use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreEnvironmentRequest;
use Fixhub\Models\Command;
use Fixhub\Models\Environment;
use Fixhub\Models\Project;
use Fixhub\Models\Link;

/**
 * Environment management controller.
 */
class EnvironmentController extends Controller
{
    /**
     * Display a listing of before/after commands for the supplied stage.
     *
     * @param Project $project
     * @param Environment $environment
     * @param string $tab
     *
     * @return Response
     */
    public function show(Project $project, Environment $environment, $tab = '')
    {
        /*
        $tmp = $environment->opposite_environments->toArray();
        foreach($tmp as $each) {
            var_dump($each);
        }
        exit;
        */
        $targetable_type = 'Fixhub\\Models\\Project';

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
            'targetable_type' => $targetable_type,
            'targetable_id'   => $project->id,
            'environments'    => $project->environments,
            'environment'     => $environment,
            'branches'        => $project->branches(),
            'tags'            => $project->tags()->reverse(),
            'optional'        => $optional,
            'tab'             => $tab,
        ];

        if ($tab == 'deployments') {
            $data['deployments'] = $environment->deployments()->paginate(15);
        } else if($tab == 'links') {
            $data['links'] = Link::all();
            $data['environmentLinks'] = json_encode($environment->opposite_environments);
        } else {
            $data['servers'] = $environment->servers;
        }

        return view('dashboard.environments.show', $data);
    }
    /**
     * Store a newly created environment in storage.
     *
     * @param  StoreEnvironmentRequest $request
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
        $targetable_id = array_pull($fields, 'targetable_id');

        $add_commands = false;
        if (isset($fields['add_commands'])) {
            $add_commands = $fields['add_commands'];
            unset($fields['add_commands']);
        }

        $target = $targetable_type::findOrFail($targetable_id);

        // In project
        if ($targetable_type == 'Fixhub\\Models\Project') {
            $this->authorize('manage', $target);
        }

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
     * @param  Environment $environment
     * @param  StoreEnvironmentRequest $request
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
