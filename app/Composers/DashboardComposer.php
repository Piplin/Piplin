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

use Fixhub\Models\Project;
use Fixhub\Models\ProjectGroup;
use Fixhub\Models\DeployTemplate;
use Fixhub\Models\User;
use Illuminate\Contracts\View\View;

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
