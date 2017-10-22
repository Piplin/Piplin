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
use Fixhub\Http\Requests\StoreProjectUserRequest;
use Illuminate\Http\Request;
use Fixhub\Models\Variable;
use Fixhub\Models\User;
use Fixhub\Models\Project;

/**
 * Project members management controller.
 */
class MemberController extends Controller
{
    /**
     * Store a newly created notification in storage.
     *
     * @param Project $project
     * @param StoreProjectUserRequest $request
     *
     * @return Response
     */
    public function store($project, StoreProjectUserRequest $request)
    {
        $user_id = $request->get('user_id');
        $user = User::findOrFail($user_id);

        $project->members()->attach($user_id);

        return $user;
    }

    /**
     * Remove the specified user from project.
     *
     * @param Project $project
     * @param int $user_id
     *
     * @return Response
     */
    public function destroy(Project $project, $user_id)
    {
        $user = User::findOrFail($user_id);

        $project->members()->detach($user_id);

        return [
            'success' => true,
        ];
    }
}
