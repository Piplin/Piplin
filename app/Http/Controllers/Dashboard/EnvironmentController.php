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
use Fixhub\Http\Requests;
use Fixhub\Http\Requests\StoreEnvironmentRequest;
use Fixhub\Models\Environment;
use Fixhub\Models\Project;

/**
 * Environment management controller.
 */
class EnvironmentController extends Controller
{
    /**
     * Display a listing of before/after commands for the supplied stage.
     *
     * @param int $targetable_id
     * @param int $environment_id     Either clone, install, activate or purge
     * @return Response
     */
    public function show($targetable_id, $environment_id)
    {
        $project = Project::findOrFail($targetable_id);
        $targetable_type = 'Fixhub\\Models\\Project';

        $environment = Environment::findOrFail($environment_id);

        $breadcrumb = [
            ['url' => route('projects', ['id' => $project->id, 'tab' => 'environments']), 'label' => $project->name],
        ];
        return view('environments.show', [
                'title'           => trans('environments.label'),
                'breadcrumb'      => $breadcrumb,
                'project'         => $project,
                'targetable_type' => $targetable_type,
                'targetable_id'   => $project->id,
                'environment'     => $environment,
                'environments'    => $project->environments,
                'servers'         => $environment->servers,
            ]);
    }
    /**
     * Store a newly created environment in storage.
     *
     * @param  StoreEnvironmentRequest $request
     * @return Response
     */
    public function store(StoreEnvironmentRequest $request)
    {
        $fields = $request->only(
            'name',
            'description',
            'default_on',
            'targetable_type',
            'targetable_id'
        );

        $targetable_type = array_pull($fields, 'targetable_type');
        $targetable_id = array_pull($fields, 'targetable_id');

        $target = $targetable_type::findOrFail($targetable_id);

        return $target->environments()->create($fields);
    }

    /**
     * Update the specified environment in storage.
     *
     * @param  int                  $variable_id
     * @param  StoreEnvironmentRequest $request
     * @return Response
     */
    public function update($variable_id, StoreEnvironmentRequest $request)
    {
        $environment = Environment::findOrFail($variable_id);

        $environment->update($request->only(
            'name',
            'description',
            'default_on'
        ));

        return $environment;
    }

    /**
     * Remove the specified environment from storage.
     *
     * @param  int $environment_id
     * @return Response
     */
    public function destroy($environment_id)
    {
        $environment = Environment::findOrFail($environment_id);

        $environment->delete();

        return [
            'success' => true,
        ];
    }
}
