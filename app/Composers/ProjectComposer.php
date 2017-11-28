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

use Cache;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Piplin\Models\Project;
use Piplin\Models\ProjectGroup;

/**
 * View composer for projects.
 */
class ProjectComposer
{
    /**
     * Generates the pending and running projects for the view.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $user              = $view->current_user ?: Auth::user();
        $projects          = $user->authorizedProjects->merge($user->personalProjects);
        $projects_by_group = [];

        foreach ($projects as $project) {
            if (!$project->targetable) {
                $projects_by_group[0]['group']      = trans('projects.ungrouped');
                $projects_by_group[0]['projects'][] = $project;
                continue;
            }
            if (!isset($projects_by_group[$project->targetable->id])) {
                $projects_by_group[$project->targetable->id]['group']    = $project->targetable->name;
                $projects_by_group[$project->targetable->id]['order']    = $project->targetable->order;
                $projects_by_group[$project->targetable->id]['projects'] = [];
            }

            $projects_by_group[$project->targetable->id]['projects'][] = $project;
        }

        usort($projects_by_group, function ($a, $b) {
            if (!isset($a['order']) || !isset($b['order'])) {
                return 0;
            }

            $al = $a['order'];
            $bl = $b['order'];
            if ($al === $bl) {
                return 0;
            }

            return ($al < $bl) ? -1 : 1;
        });

        $view->withProjects($projects_by_group);
    }
}
