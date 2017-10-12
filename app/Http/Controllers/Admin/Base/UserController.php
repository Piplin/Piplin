<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Admin\Base;

use Fixhub\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

/**
 * User base controller.
 */
class UserController extends Controller
{
    protected $subMenu = [];

    public function __construct()
    {
        $this->subMenu = [
            'users' => [
                'title' => trans('users.manage'),
                'url' => route('admin.users.index'),
                'icon' => 'ion-android-person',
                'active' => false,
            ],
            'providers' => [
                'title' => trans('providers.manage'),
                'url' => route('admin.providers.index'),
                'icon' => 'ion-android-person-add',
                'active' => false,
            ],
        ];

        View::share([
            'sub_title' => trans('users.manage'),
            'sub_menu'  => $this->subMenu,
        ]);
    }
}
