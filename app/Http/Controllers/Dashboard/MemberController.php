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
use Illuminate\Http\Request;
use Fixhub\Models\Variable;
use Fixhub\Models\User;

/**
 * Project members management controller.
 */
class MemberController extends Controller
{
    /**
     * Remove the specified user from project.
     *
     * @param int $user_id
     *
     * @return Response
     */
    public function destroy(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);
        $project_id = $request->get('project_id');

        var_dump($project_id);


        return [
            'success' => true,
        ];
    }
}