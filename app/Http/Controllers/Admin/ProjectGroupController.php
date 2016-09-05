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

use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreProjectGroupRequest;
use Fixhub\Models\ProjectGroup;
use Illuminate\Http\Request;

/**
 * Project group management controller.
 */
class ProjectGroupController extends Controller
{
    /**
     * Shows the create project group view.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return $this->index($request)->withAction('create');
    }

    /**
     * Display a listing of the groups.
     *
     * @return Response
     */
    public function index()
    {
        $groups = ProjectGroup::orderBy('order')
                    ->get();

        return view('admin.groups.index', [
            'title'  => trans('groups.manage'),
            'groups' => $groups,
        ]);
    }

    /**
     * Store a newly created group in storage.
     *
     * @param  StoreProjectGroupRequest $request
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
     * @param  int               $group_id
     * @param  StoreProjectGroupRequest $request
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
     * @param  Request  $request
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
     * @param  int      $group_id
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
