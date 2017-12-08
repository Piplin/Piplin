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
use Piplin\Http\Requests\StoreProjectUserRequest;
use Piplin\Models\Project;
use Piplin\Models\User;
use Piplin\Models\Variable;

/**
 * Project members management controller.
 */
class MemberController extends Controller
{
    /**
     * Store a newly created notification in storage.
     *
     * @param Project                 $project
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
     * @param User    $user
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
