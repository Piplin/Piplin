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
use Piplin\Models\Task;

/**
 * View composer for timeline.
 */
class TimelineComposer
{
    /**
     * Sets the timeline data into view variables.
     *
     * @param \Illuminate\Contracts\View\View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $data = $this->buildTimelineData($view);

        $view->with('latest', $data[0]);
        $view->with('tasks_raw', $data[1]);
    }

    /**
     * Builds the data for the timline.
     *
     * @param \Illuminate\Contracts\View\View $view
     *
     * @return array
     */
    private function buildTimelineData(View $view)
    {
        $user = $view->current_user ?: Auth::user();

        $tasks = Task::whereNotNull('started_at');

        if (!$view->in_admin) {
            $personalProjectIds = $user->personalProjects->pluck('id')->toArray();
            $projectIds         = array_merge($personalProjectIds, $user->authorizedProjects->pluck('id')->toArray());
            $tasks        = $tasks->whereIn('project_id', $projectIds);
        }

        $tasks = $tasks->orderBy('started_at', 'DESC')
                        ->paginate(10);

        $deploys_by_date = [];
        foreach ($tasks as $task) {
            $date = $task->started_at->format('Y-m-d');

            if (!isset($deploys_by_date[$date])) {
                $deploys_by_date[$date] = [];
            }

            $deploys_by_date[$date][] = $task;
        }

        return [$deploys_by_date, $tasks];
    }
}
