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
    private $subMenus = [];

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
        $subTitle = '';
        $subMenu = [];

        $name = $view->name();

        //User collection
        if ($name == 'admin.users.index') {
            $subTitle = trans('users.manage');
            $subMenu = $this->getSubMenu('user', 'users');
        } elseif ($name == 'admin.providers.index') {
            $subTitle = trans('providers.manage');
            $subMenu = $this->getSubMenu('user', 'providers');
        }

        // Project collection
        if ($name == 'admin.projects.index') {
            $subTitle = trans('projects.manage');
            $subMenu = $this->getSubMenu('project', 'projects');
        } elseif ($name == 'admin.groups.index') {
            $subTitle = trans('projects.manage');
            $subMenu = $this->getSubMenu('project', 'groups');
        } elseif ($name == 'admin.templates.index') {
            $subTitle = trans('templates.manage');
            $subMenu = $this->getSubMenu('project', 'templates');
        } elseif ($name == 'admin.keys.index') {
            $subTitle = trans('keys.manage');
            $subMenu = $this->getSubMenu('project', 'keys');
        }

        // Misc collection
        if ($name == 'admin.links.index') {
            $subTitle = trans('admin.misc');
            $subMenu = $this->getSubMenu('misc', 'links');
        } elseif ($name == 'admin.tips.index') {
            $subTitle = trans('admin.misc');
            $subMenu = $this->getSubMenu('misc', 'tips');
        }

        $view->with('sub_title', $subTitle);
        $view->with('sub_menu', $subMenu);
    }

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
