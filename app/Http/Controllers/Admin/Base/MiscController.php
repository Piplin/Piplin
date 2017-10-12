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
 * Misc base controller.
 */
class MiscController extends Controller
{
    /**
     * Array of sub-menu items.
     *
     * @var array
     */
    protected $subMenu = [];

    /**
     * Creates a misc controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->subMenu = [
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

        View::share([
            'sub_title' => trans('links.manage'),
            'sub_menu'  => $this->subMenu,
        ]);
    }
}
