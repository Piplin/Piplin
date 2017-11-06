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

use Cache;
use Fixhub\Models\ProjectGroup;
use Fixhub\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * View composer for projects.
 */
class ProjectComposer
{
    /**
     * Generates the pending and deploying projects for the view.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $user = $view->current_user ?: Auth::user();

        $projects = $user->is_admin ? Project::all() : array_merge($user->authorized_projects->toArray(), $user->personal_projects->toArray());

        $projects_by_group = [];

        foreach ($projects as $project) {
            if (!$project->targetable) {
                $projects_by_group[0]['group'] = trans('projects.ungrouped');
                $projects_by_group[0]['projects'][] = $project;
                continue;
            }
            if (!isset($projects_by_group[$project->targetable->id])) {
                $projects_by_group[$project->targetable->id]['group'] = $project->targetable->name;
                $projects_by_group[$project->targetable->id]['order'] = $project->targetable->order;
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
            if ($al == $bl) {
                return 0;
            }
            return ($al < $bl) ? -1 : 1;
        });

        $view->withProjects($projects_by_group);
    }
}
