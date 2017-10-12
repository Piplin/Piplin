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

use Fixhub\Http\Controllers\Admin\Base\UserController as Controller;
use Fixhub\Bus\Events\UserWasCreatedEvent;
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
        $this->subMenu['users']['active'] = true;

        $users = User::orderBy('id', 'desc')
                    ->paginate(config('fixhub.items_per_page', 10));

        return view('admin.users.index', [
            'title'     => trans('users.manage'),
            'users_raw' => $users,
            'users'     => $users->toJson(),
            'levels'    => [
                User::LEVEL_USER     => trans('users.level.user'),
                User::LEVEL_OPERATOR => trans('users.level.operator'),
                User::LEVEL_ADMIN    => trans('users.level.admin'),
            ],
            'sub_title' => trans('users.manage'),
            'sub_menu'  => $this->subMenu,
        ]);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param StoreUserRequest $request
     *
     * @return Response
     */
    public function store(StoreUserRequest $request)
    {
        $fields = $request->only(
            'name',
            'level',
            'nickname',
            'email',
            'password'
        );
        $fields['password'] = bcrypt($fields['password']);

        $user = User::create($fields);

        event(new UserWasCreatedEvent($user, $request->get('password')));

        return $user;
    }

    /**
     * Update the specified user in storage.
     *
     * @param int              $user_id
     * @param StoreUserRequest $request
     *
     * @return Response
     */
    public function update($user_id, StoreUserRequest $request)
    {
        $user = User::findOrFail($user_id);

        $fields = $request->only(
            'name',
            'level',
            'nickname',
            'email',
            'password'
        );

        if (array_key_exists('password', $fields)) {
            if (empty($fields['password'])) {
                unset($fields['password']);
            } else {
                $fields['password'] = bcrypt($fields['password']);
            }
        }

        $user->update($fields);

        return $user;
    }

    /**
     * Remove the specified user from storage.
     *
     * @param int $user_id
     *
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
