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
use Piplin\Models\DeployTemplate;
use Piplin\Models\Project;
use Piplin\Models\ProjectGroup;
use Piplin\Models\User;

/**
 * View composer for the dashboard index.
 */
class DashboardComposer
{
    /**
     * Generates the group listing for the view.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('project_count', Project::count());
        $view->with('template_count', DeployTemplate::count());
        $view->with('group_count', ProjectGroup::count());
        $view->with('user_count', User::count());
    }
}
