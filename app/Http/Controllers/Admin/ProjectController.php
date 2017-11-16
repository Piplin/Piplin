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

use Fixhub\Bus\Jobs\SetupSkeletonJob;
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
     * Shows all projects.
     *
     * @param \Illuminate\Http\Request $request
     *
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
     * Shows the create project view.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return $this->index($request)->withAction('create');
    }

    /**
     * Store a newly created project in storage.
     *
     * @param  StoreProjectRequest $request
     *
     * @return Response
     */
    public function store(StoreProjectRequest $request)
    {
        $fields = $request->only(
            'name',
            'repository',
            'branch',
            'targetable_id',
            'key_id',
            'deploy_path',
            'builds_to_keep',
            'url',
            'build_url',
            'template_id',
            'allow_other_branch'
        );

        $template_id = null;
        if (array_key_exists('template_id', $fields)) {
            $template_id = array_pull($fields, 'template_id');
        }

        $skeleton = DeployTemplate::find($template_id);

        $group_id = array_pull($fields, 'targetable_id');

        if ($group_id && $group = ProjectGroup::find($group_id)) {
            $project = $group->projects()->create($fields);
        } else {
            $project = Project::create($fields);
        }
        
        dispatch(new SetupSkeletonJob($project, $skeleton));

        return $project;
    }

    /**
     * Clone a new project based on skeleton.
     *
     * @param Project $skeleton
     * @param Request $request
     *
     * @return Response
     */
    public function clone(Project $skeleton, Request $request)
    {
        $fields = $request->only('name', 'type');
        $type = array_pull($fields, 'type');

        if (empty($fields['name'])) {
            $fields['name'] = $skeleton->name . '_Clone';
        }

        if ($type == 'project') {
            $fields['targetable_type'] = $skeleton->targetable_type;
            $fields['targetable_id'] = $skeleton->targetable_id;
            $fields['key_id'] = $skeleton->key_id;
            $fields['deploy_path'] = $skeleton->deploy_path;
            $fields['repository'] = $skeleton->repository;
            $target = Project::create($fields);
        } else {
            $target = DeployTemplate::create($fields);
        }

        dispatch(new SetupSkeletonJob($target, $skeleton));

        return redirect()->route($type == 'template' ? 'admin.templates.show' : 'projects', [
            'id' => $target->id,
        ]);
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
            'targetable_id',
            'deploy_path',
            'builds_to_keep',
            'url',
            'build_url',
            'allow_other_branch'
        ));

        return $project;
    }

    /**
     * Remove the specified model from storage.
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
}
