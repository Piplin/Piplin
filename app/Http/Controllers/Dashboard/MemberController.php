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
        $user_ids = $request->get('user_ids');

        $users = User::whereIn('id', $user_ids)->get();

        $project->members()->attach($user_ids);

        return $users;
    }

    /**
     * Remove the specified user from project.
     *
     * @param Project $project
     * @param User $user
     *
     * @return Response
     */
    public function destroy(Project $project, User $user)
    {
        $project->members()->detach($user->id);

        return [
            'success' => true,
        ];
    }
}
