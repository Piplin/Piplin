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
use Fixhub\Models\Link;
use Fixhub\Models\Project;
use Fixhub\Models\Tip;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * View composer for the side bar.
 */
class SidebarComposer
{
    const CACHE_MINUTES = 10;

    /**
     * Generates the pending and deploying projects for the view.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->withLinks($this->getLinks());
        $view->withTip($this->getRandomTip());

        $projects = Project::all();
        $projects_by_group = [];
        $projects_need_approve = [];
        foreach ($projects as $project) {
            if (!$project->group) {
                $projects_by_group[0]['group'] = trans('dashboard.projects');
                $projects_by_group[0]['projects'][] = $project;
                continue;
            }
            if (!isset($projects_by_group[$project->group->id])) {
                $projects_by_group[$project->group->id]['group'] = $project->group->name;
                $projects_by_group[$project->group->id]['order'] = $project->group->order;
                $projects_by_group[$project->group->id]['projects'] = [];
            }

            if ($project->need_approve) {
                $projects_need_approve[] = $project;
            }
            $projects_by_group[$project->group->id]['projects'][] = $project;
        }

        usort($projects_by_group, function ($a, $b) {
            $al = $a['order'];
            $bl = $b['order'];
            if ($al == $bl) {
                return 0;
            }
            return ($al < $bl) ? -1 : 1;
        });

        $view->withProjectsNeedApprove($projects_need_approve);
        $view->withProjects($projects_by_group);
    }

    /**
     * Gets the groups.
     *
     * @return array
     */
    protected function getGroups()
    {
        $usedProjectGroups = Project::where('group_id', '>', 0)->groupBy('group_id')->pluck('group_id');
        return ProjectGroup::whereIn('id', $usedProjectGroups)->orderBy('order')->get();
    }

    /**
     * Gets a random tip.
     *
     * @return array
     */
    protected function getRandomTip()
    {
        $tips = Cache::remember('random_tip', self::CACHE_MINUTES, function () {
            return Tip::where('status', true)->orderBy('id', 'desc')->take(20)->get();
        });

        return ($tips && $tips->count() > 0) ? $tips->random() : null;
    }

    /**
     * Gets the links.
     *
     * @return array
     */
    protected function getLinks()
    {
        return Cache::remember('links', self::CACHE_MINUTES, function () {
            return Link::orderBy('order', 'asc')->take(10)->get();
        });
    }
}
