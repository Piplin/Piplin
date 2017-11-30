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
use Illuminate\Support\Facades\Auth;
use Piplin\Bus\Jobs\SetupSkeletonJob;
use Piplin\Http\Controllers\Controller;
use Piplin\Http\Requests\StoreProjectRequest;
use Piplin\Models\Command;
use Piplin\Models\Task;
use Piplin\Models\Project;

/**
 * The controller of projects.
 */
class ProjectController extends Controller
{
    /**
     * The details of an individual project.
     *
     * @param Project $project
     * @param string  $tab
     *
     * @return View
     */
    public function show(Project $project, $tab = '')
    {
        $optional = $project->commands->filter(function (Command $command) {
            return $command->optional;
        });

        $data = [
            'project'         => $project,
            'targetable_type' => get_class($project),
            'targetable_id'   => $project->id,
            'optional'        => $optional,
            'tasks'           => $this->getLatest($project),
            'tab'             => $tab,
            'breadcrumb'      => [
                ['url' => route('projects', ['id' => $project->id]), 'label' => $project->name],
            ],
        ];

        $data['environments'] = $project->environments;
        if ($tab === 'hooks') {
            $data['hooks'] = $project->hooks;
            $data['title'] = trans('hooks.label');
        } elseif ($tab === 'members') {
            $data['members'] = $project->members->toJson();
            $data['title']   = trans('hooks.label');
        } elseif ($tab === 'environments') {
            $data['title'] = trans('environments.label');
        }

        return view('dashboard.projects.show', $data);
    }

    /**
     * Store a newly created project in storage.
     *
     * @param StoreProjectRequest $request
     *
     * @return Response
     */
    public function store(StoreProjectRequest $request)
    {
        $fields = $request->only(
            'name',
            'repository',
            'branch',
            'deploy_path',
            'allow_other_branch'
        );

        $skeleton = null;

        $project = Auth::user()->personalProjects()->create($fields);

        dispatch(new SetupSkeletonJob($project, $skeleton));

        return $project;
    }

    /**
     * Update the specified project in storage.
     *
     * @param Project             $project
     * @param StoreProjectRequest $request
     *
     * @return Response
     */
    public function update(Project $project, StoreProjectRequest $request)
    {
        $project->update($request->only(
            'name',
            'repository',
            'branch',
            'deploy_path',
            'allow_other_branch'
        ));

        return $project;
    }

    /**
     * Remove the specified project from storage.
     *
     * @param Project $project
     *
     * @return Response
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return [
            'success' => true,
        ];
    }

    /**
     * Gets the latest deployments for a project.
     *
     * @param  Project $project
     * @param  int     $paginate
     * @return array
     */
    private function getLatest(Project $project, $paginate = 15)
    {
        return Task::where('project_id', $project->id)
                           ->with('user')
                           ->whereNotNull('started_at')
                           ->orderBy('started_at', 'DESC')
                           ->paginate($paginate);
    }
}
