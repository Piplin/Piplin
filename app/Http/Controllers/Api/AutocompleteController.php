<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Api;

use Fixhub\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Fixhub\Models\User;

/**
 * The Auto-complete controller.
 */
class AutocompleteController extends Controller
{
    /**
     * Search users by key word.
     *
     * @param  Request  $request
     * @return Response
     */
    public function users(Request $request)
    {
        $users = User::where('name', 'like', $request->get('q') . '%')->get(['id', 'name'])->toArray();

        return Response::json($users);
    }
}
