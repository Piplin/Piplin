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

use Auth;
use Illuminate\Contracts\View\View;
use Piplin\Models\Task;

/**
 * View composer for the siderbar bar.
 */
class SidebarComposer
{
    /**
     * Generates the pending and running projects for the view.
     *
     * @param  \Illuminate\Contracts\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $running       = $this->getRunning();
        $running_count = count($running);

        $view->with('running', $running);
        $view->with('todo_count', $running_count);
    }

    /**
     * Gets running tasks.
     *
     * @return array
     */
    private function getRunning()
    {
        return $this->getStatus([Task::PENDING, Task::RUNNING]);
    }

    /**
     * Gets tasks with a supplied status.
     *
     * @param  array|int $status
     * @return array
     */
    private function getStatus($status)
    {
        return Task::whereNotNull('started_at')
                           ->whereIn('status', is_array($status) ? $status : [$status])
                           ->orderBy('started_at', 'DESC')
                           ->get();
    }
}
