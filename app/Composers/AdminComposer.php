<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Composers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as Vshare;

/**
 * The composer for the admin.
 */
class AdminComposer
{
    /**
     * Array of sub-menu items.
     *
     * @var array
     */
    private $subMenus = [];

    /**
     * Create a new admin composer instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->subMenus = [
            'index' => [
                'title'  => trans('admin.home'),
                'url'    => route('admin'),
                'icon'   => 'home',
                'active' => false,
                'children' => [],
            ],
            'projects' => [
                'title' => trans('projects.manage'),
                'url'   => route('admin.projects.index'),
                'icon'  => 'project',
                'active' => false,
                'children' => [
                    'projects' => [
                        'title'  => trans('projects.manage'),
                        'url'    => route('admin.projects.index'),
                        'icon'   => 'project',
                        'active' => false,
                    ],
                    'groups' => [
                        'title'  => trans('groups.manage'),
                        'url'    => route('admin.groups.index'),
                        'icon'   => 'group',
                        'active' => false,
                    ],
                ],
            ],
            'resources' => [
                'title' => trans('admin.resources'),
                'url'   => route('admin.templates.index'),
                'icon'  => 'template',
                'active' => false,
                'children' => [
                    'templates' => [
                        'title'  => trans('templates.manage'),
                        'url'    => route('admin.templates.index'),
                        'icon'   => 'template',
                        'active' => false,
                    ],
                    'cabinets' => [
                        'title'  => trans('cabinets.manage'),
                        'url'    => route('admin.cabinets.index'),
                        'icon'   => 'cabinet',
                        'active' => false,
                    ],
                    'keys' => [
                        'title'  => trans('keys.manage'),
                        'url'    => route('admin.keys.index'),
                        'icon'   => 'key',
                        'active' => false,
                    ],
                ],
            ],
            'users' => [
                'title' => trans('users.manage'),
                'url'   => route('admin.users.index'),
                'icon'  => 'users',
                'active' => false,
                'children' => [
                    'users' => [
                        'title'  => trans('users.manage'),
                        'url'    => route('admin.users.index'),
                        'icon'   => 'user',
                        'active' => false,
                    ],
                    'providers' => [
                        'title'  => trans('providers.manage'),
                        'url'    => route('admin.providers.index'),
                        'icon'   => 'provider',
                        'active' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Sets the logged in user into a view variable.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $name = $view->name();

        if ($name === 'admin.index') {
            $current_menu = 'index';
        }

        //User collection
        if ($name === 'admin.users.index') {
            $current_menu = 'users';
        } elseif ($name === 'admin.providers.index') {
            $current_menu = 'users';
        }

        // Project collection
        if ($name === 'admin.projects.index') {
            $current_menu = 'projects';
        } elseif (in_array($name, ['admin.groups.index', 'admin.groups.show'], true)) {
            $current_menu = 'projects';
        }

        // Deployment collection
        if (in_array($name, ['admin.templates.index', 'admin.templates.show'], true)) {
            $current_menu = 'resources';
        } elseif ($name === 'admin.keys.index') {
            $current_menu = 'resources';
        } elseif (in_array($name, ['admin.cabinets.index', 'admin.cabinets.show'], true)) {
            $current_menu = 'resources';
        }

        Vshare::share([
            'sub_menu' => $this->getSubMenu(),
            'current_menu' => $current_menu,
        ]);
    }

    /**
     * Returns a submenu by collection and key.
     *
     * @return array
     */
    private function getSubMenu()
    {
        return $this->subMenus;
    }
}
