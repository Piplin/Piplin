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
 * Project base controller.
 */
class ProjectController extends Controller
{
    protected $subMenu = [];

    public function __construct()
    {
        $this->subMenu = [
            'projects' => [
                'title' => trans('projects.manage'),
                'url' => route('admin.projects.index'),
                'icon' => 'ion-cube',
                'active' => false,
            ],
            'groups' => [
                'title' => trans('groups.manage'),
                'url' => route('admin.groups.index'),
                'icon' => 'ion-ios-browsers-outline',
                'active' => false,
            ],
            'templates' => [
                'title' => trans('templates.manage'),
                'url' => route('admin.templates.index'),
                'icon' => 'ion-ios-paper-outline',
                'active' => false,
            ],
            'keys' => [
                'title' => trans('keys.manage'),
                'url' => route('admin.keys.index'),
                'icon' => 'ion-key',
                'active' => false,
            ],
        ];
        
        View::share([
            'sub_title' => trans('projects.manage'),
            'sub_menu'  => $this->subMenu,
        ]);
    }
}
