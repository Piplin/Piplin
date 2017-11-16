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

use Carbon\Carbon;
use Fixhub\Models\Deployment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * The composer for the project summary.
 */
class ProjectSummaryComposer
{
    private $now;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->now = Carbon::now();
    }

    /**
     * Sets the logged in user into a view variable.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('today', $this->getBetweenDates($view->project->id, $this->now, $this->now));
        $view->with('last_week', $this->getLastWeekCount($view->project->id));
        $view->with('total_count', $this->getTotalCount($view->project->id));
    }

    /**
     * Gets the number of times a project has been deployed in the last week.
     *
     * @param  int $project_id
     * @return int
     * @see DeploymentRepository::getBetweenDates()
     */
    private function getLastWeekCount($project_id)
    {
        $lastWeek  = Carbon::now()->subWeek();
        $yesterday = Carbon::now()->yesterday();

        return $this->getBetweenDates($project_id, $lastWeek, $yesterday);
    }

    /**
     * Gets the number of times a project has been deployed.
     *
     * @param int $project_id
     *
     * @return int
     */
    private function getTotalCount($project_id)
    {
        return Deployment::where('project_id', $project_id)
                            ->count();
    }

    /**
     * Gets the number of times a project has been deployed between the specified dates.
     *
     * @param  int    $project_id
     * @param  Carbon $startDate
     * @param  Carbon $endDate
     * @return int
     */
    private function getBetweenDates($project_id, Carbon $startDate, Carbon $endDate)
    {
        return Deployment::where('project_id', $project_id)
                           ->where('started_at', '>=', $startDate->format('Y-m-d') . ' 00:00:00')
                           ->where('started_at', '<=', $endDate->format('Y-m-d') . ' 23:59:59')
                           ->count();
    }
}
