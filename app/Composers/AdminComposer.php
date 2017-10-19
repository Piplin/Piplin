<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Composers;

use Illuminate\Contracts\View\View;

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
        $this->subMenus['user'] = [
                'users'=> [
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
                ]
            ];

        $this->subMenus['project'] = [
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
        ];

        $this->subMenus['deployment'] = [
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

        $this->subMenus['misc'] = [
            'links' => [
                'title' => trans('links.manage'),
                'url' => route('admin.links.index'),
                'icon' => 'ion-link',
                'active' => false,
            ],
            'tips' => [
                'title' => trans('tips.manage'),
                'url' => route('admin.tips.index'),
                'icon' => 'ion-ios-lightbulb-outline',
                'active' => false,
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
        $subMenu = [];

        $name = $view->name();

        //User collection
        if ($name == 'admin.users.index') {
            $subMenu = $this->getSubMenu('user', 'users');
        } elseif ($name == 'admin.providers.index') {
            $subMenu = $this->getSubMenu('user', 'providers');
        }

        // Project collection
        if ($name == 'admin.projects.index') {
            $subMenu = $this->getSubMenu('project', 'projects');
        } elseif (in_array($name, ['admin.groups.index', 'admin.groups.show'])) {
            $subMenu = $this->getSubMenu('project', 'groups');
        }

        // Deployment collection
        if (in_array($name, ['admin.templates.index', 'admin.templates.show'])) {
            $subMenu = $this->getSubMenu('deployment', 'templates');
        } elseif ($name == 'admin.keys.index') {
            $subMenu = $this->getSubMenu('deployment', 'keys');
        }

        // Misc collection
        if ($name == 'admin.links.index') {
            $subMenu = $this->getSubMenu('misc', 'links');
        } elseif ($name == 'admin.tips.index') {
            $subMenu = $this->getSubMenu('misc', 'tips');
        }

        $view->with('sub_menu', $subMenu);
    }

    /**
     * Returns a submenu by collection and key.
     *
     * @return array
     */
    private function getSubMenu($collection, $key = '')
    {
        if (!isset($this->subMenus[$collection])) {
            return;
        }

        if (!empty($key) && isset($this->subMenus[$collection][$key])) {
            $this->subMenus[$collection][$key]['active'] = true;
        }

        return $this->subMenus[$collection];
    }
}
