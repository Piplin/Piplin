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
use Fixhub\Models\Deployment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * View composer for timeline.
 */
class TimelineComposer
{
    /**
     * Sets the timeline data into view variables.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $this->buildTimelineData($view);

        $view->with('latest', $data[0]);
        $view->with('deployments_raw', $data[1]);
    }

    /**
     * Builds the data for the timline.
     *
     * @return array
     */
    private function buildTimelineData(View $view)
    {
        $user = $view->current_user ?: Auth::user();

        $deployments = Deployment::whereNotNull('started_at');

        if (!$view->in_admin) {
            $personalProjectIds = $user->personalProjects->pluck('id')->toArray();
            $projectIds = array_merge($personalProjectIds, $user->authorizedProjects->pluck('id')->toArray());
            $deployments = $deployments->whereIn('project_id', $projectIds);
        }

        $deployments = $deployments->orderBy('started_at', 'DESC')
                        ->paginate(10);

        $deploys_by_date = [];
        foreach ($deployments as $deployment) {
            $date = $deployment->started_at->format('Y-m-d');

            if (!isset($deploys_by_date[$date])) {
                $deploys_by_date[$date] = [];
            }

            $deploys_by_date[$date][] = $deployment;
        }

        return [$deploys_by_date, $deployments];
    }
}