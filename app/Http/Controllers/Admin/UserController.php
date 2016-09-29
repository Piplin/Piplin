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

use Fixhub\Bus\Events\UserWasCreated;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreUserRequest;
use Fixhub\Models\User;

/**
 * User management controller.
 */
class UserController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @return Response
     */
    public function index()
    {
        $users = User::orderBy('id', 'desc')
                    ->paginate(config('fixhub.items_per_page', 10));
        return view('admin.users.index', [
            'title'     => trans('users.manage'),
            'users_raw' => $users,
            'users'     => $users->toJson(),
            'levels'    => [
                User::LEVEL_ADMIN    => trans('users.level.admin'),
                User::LEVEL_OPERATOR => trans('users.level.operator'),
                User::LEVEL_USER     => trans('users.level.user'),
            ],
        ]);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  StoreUserRequest $request
     * @return Response
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->only(
            'name',
            'level',
            'nickname',
            'email',
            'password'
        ));

        event(new UserWasCreated($user, $request->get('password')));

        return $user;
    }

    /**
     * Update the specified user in storage.
     *
     * @param  int              $user_id
     * @param  StoreUserRequest $request
     * @return Response
     */
    public function update($user_id, StoreUserRequest $request)
    {
        $user = User::findOrFail($user_id);

        $user->update($request->only(
            'name',
            'level',
            'nickname',
            'email',
            'password'
        ));

        return $user;
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int      $user_id
     * @return Response
     */
    public function destroy($user_id)
    {
        $user = User::findOrFail($user_id);

        $user->delete();

        return [
            'success' => true,
        ];
    }
}
