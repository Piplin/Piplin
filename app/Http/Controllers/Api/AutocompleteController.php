<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Piplin\Http\Controllers\Controller;
use Piplin\Models\Cabinet;
use Piplin\Models\User;

/**
 * The Auto-complete controller.
 */
class AutocompleteController extends Controller
{
    /**
     * Search users by key word.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function users(Request $request)
    {
        $q = trim($request->get('q'));

        $users = User::where('name', 'like', $q . '%')->orWhere('nickname', 'like', $q .'%')->get(['id', 'name', 'nickname'])->toArray();

        return Response::json($users);
    }

    /**
     * Search cabinets by key word.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cabinets(Request $request)
    {
        $q = trim($request->get('q'));

        if (empty($q)) {
            $cabinets = Cabinet::orderBy('id', 'desc')->limit(10)->get(['id', 'name']);
        } else {
            $cabinets = Cabinet::where('name', 'like', '%' . $q . '%')->get(['id', 'name']);
        }

        return Response::json($cabinets->toArray());
    }
}
