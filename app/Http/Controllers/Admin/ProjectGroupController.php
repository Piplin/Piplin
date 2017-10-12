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

use Fixhub\Http\Controllers\Admin\Base\ProjectController as Controller;
use Fixhub\Http\Requests\StoreProjectGroupRequest;
use Fixhub\Models\DeployTemplate;
use Fixhub\Models\Key;
use Fixhub\Models\Project;
use Fixhub\Models\ProjectGroup;
use Illuminate\Http\Request;

/**
 * Project group management controller.
 */
class ProjectGroupController extends Controller
{
    /**
     * Display a listing of the groups.
     *
     * @return Response
     */
    public function index()
    {
        $this->subMenu['groups']['active'] = true;

        $groups = ProjectGroup::orderBy('order')
                    ->paginate(config('fixhub.items_per_page', 10));

        return view('admin.groups.index', [
            'title'    => trans('groups.manage'),
            'groups'   => $groups,
            'sub_menu' => $this->subMenu,
        ]);
    }

    /**
     * Show the group details.
     *
     * @param int $group_id
     * @param string $tab
     *
     * @return Response
     */
    public function show($group_id, $tab = '')
    {
        $group = ProjectGroup::findOrFail($group_id);

        $projects = Project::where('group_id', $group_id)
                    ->orderBy('name')
                    ->paginate(config('fixhub.items_per_page', 10));

        $groups = ProjectGroup::orderBy('order')
                    ->paginate(config('fixhub.items_per_page', 10));

        $templates = DeployTemplate::orderBy('name')
                    ->get();

        $keys = Key::orderBy('name')
                    ->get();

        return view('admin.groups.show', [
            'breadcrumb'   => [
                ['url' => route('admin.groups.index'), 'label' => trans('groups.manage')],
            ],
            'title'        => $group->name,
            'projects_raw' => $projects,
            'projects'     => $projects->toJson(), // Because ProjectPresenter toJson() is not working in the view
            'groups'       => $groups,
            'group'        => $group,
            'templates'    => $templates,
            'keys'         => $keys,
            'tab'          => $tab,
        ]);
    }

    /**
     * Shows the create project group view.
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
     * Store a newly created group in storage.
     *
     * @param  StoreProjectGroupRequest $request
     *
     * @return Response
     */
    public function store(StoreProjectGroupRequest $request)
    {
        return ProjectGroup::create($request->only(
            'name'
        ));
    }

    /**
     * Update the specified group in storage.
     *
     * @param int                      $group_id
     * @param StoreProjectGroupRequest $request
     *
     * @return Response
     */
    public function update($group_id, StoreProjectGroupRequest $request)
    {
        $group = ProjectGroup::findOrFail($group_id);

        $group->update($request->only(
            'name'
        ));

        return $group;
    }

    /**
     * Re-generates the order for the supplied groups.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Response
     */
    public function reorder(Request $request)
    {
        $order = 0;

        foreach ($request->get('groups') as $group_id) {
            $group = ProjectGroup::findOrFail($group_id);
            $group->update([
                'order' => $order,
            ]);

            $order++;
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Remove the specified group from storage.
     *
     * @param int $group_id
     *
     * @return Response
     */
    public function destroy($group_id)
    {
        $group = ProjectGroup::findOrFail($group_id);

        $group->delete();

        return [
            'success' => true,
        ];
    }
}
