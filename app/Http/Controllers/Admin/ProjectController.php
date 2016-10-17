<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Admin;

use Fixhub\Bus\Jobs\SetupProject;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreProjectRequest;
use Fixhub\Models\Key;
use Fixhub\Models\ProjectGroup;
use Fixhub\Models\Project;
use Fixhub\Models\DeployTemplate;
use Illuminate\Http\Request;

/**
 * The controller for managging projects.
 */
class ProjectController extends Controller
{
    /**
     * Shows the create project view.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return $this->index($request)->withAction('create');
    }

    /**
     * Shows all projects.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $projects = Project::orderBy('name')
                    ->paginate(config('fixhub.items_per_page', 10));

        $keys = Key::orderBy('name')
                    ->get();

        $groups = ProjectGroup::orderBy('order')
                    ->get();

        $templates = DeployTemplate::orderBy('name')
                    ->get();

        return view('admin.projects.index', [
            'is_secure'    => $request->secure(),
            'title'        => trans('projects.manage'),
            'keys'         => $keys,
            'templates'    => $templates,
            'groups'       => $groups,
            'projects_raw' => $projects,
            'projects'     => $projects->toJson(), // Because ProjectPresenter toJson() is not working in the view
        ]);
    }

    /**
     * Store a newly created project in storage.
     *
     * @param  StoreProjectRequest $request
     * @return Response
     */
    public function store(StoreProjectRequest $request)
    {
        $fields = $request->only(
            'name',
            'repository',
            'branch',
            'group_id',
            'key_id',
            'builds_to_keep',
            'url',
            'build_url',
            'template_id',
            'allow_other_branch',
            'need_approve'
        );

        $template_id = null;
        if (array_key_exists('template_id', $fields)) {
            $template_id = array_pull($fields, 'template_id');
        }

        $project = Project::create($fields);

        $template = DeployTemplate::find($template_id);

        if ($template) {
            dispatch(new SetupProject(
                $project,
                $template
            ));
        }

        return $project;
    }

    /**
     * Update the specified project in storage.
     *
     * @param  int                 $project_id
     * @param  StoreProjectRequest $request
     * @return Response
     */
    public function update($project_id, StoreProjectRequest $request)
    {
        $project = Project::findOrFail($project_id);

        $project->update($request->only(
            'name',
            'repository',
            'branch',
            'group_id',
            'key_id',
            'builds_to_keep',
            'url',
            'build_url',
            'allow_other_branch',
            'need_approve'
        ));

        return $project;
    }

    /**
     * Remove the specified model from storage.
     *
     * @param  int      $project_id
     * @return Response
     */
    public function destroy($project_id)
    {
        $project = Project::findOrFail($project_id);

        $project->delete();

        return [
            'success' => true,
        ];
    }
}
