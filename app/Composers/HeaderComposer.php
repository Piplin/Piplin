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

use Auth;
use Fixhub\Models\Deployment;
use Fixhub\Models\Issue;
use Illuminate\Contracts\View\View;

/**
 * View composer for the header bar.
 */
class HeaderComposer
{
    /**
     * Generates the pending and deploying projects for the view.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $pending = $this->getPending();
        $pending_count = count($pending);

        $view->with('pending', $pending);
        $view->with('pending_count', $pending_count);

        $deploying = $this->getRunning();
        $deploying_count = count($deploying);

        $view->with('deploying', $deploying);
        $view->with('deploying_count', $deploying_count);

        $view->with('todo_count', $pending_count + $deploying_count);
    }

    /**
     * Gets pending deployments.
     *
     * @return array
     */
    private function getPending()
    {
        return $this->getStatus(Deployment::PENDING);
    }

    /**
     * Gets running deployments.
     *
     * @return array
     */
    private function getRunning()
    {
        return $this->getStatus(Deployment::DEPLOYING);
    }

    /**
     * Gets deployments with a supplied status.
     *
     * @param  int   $status
     * @return array
     */
    private function getStatus($status)
    {
        $raw_sql = 'project_id IN (SELECT id FROM projects WHERE deleted_at IS NULL)';

        return Deployment::whereRaw($raw_sql)
                           ->where('status', $status)
                           ->whereNotNull('started_at')
                           ->orderBy('started_at', 'DESC')
                           ->get();
    }

    /**
    * Gets issues with a supplied identity.
    *
    * @param  string $identity
    * @return array
    */
    private function getIssues($identity)
    {
        return Issue::where($identity . '_id', Auth::user()->id)->get();
    }

    /**
    * Gets issues count with a supplied identity.
    *
    * @param  string $identity
    * @return array
    */
    private function getIssuesCount($identity)
    {
        return Issue::where($identity . '_id', Auth::user()->id)->count();
    }
}
