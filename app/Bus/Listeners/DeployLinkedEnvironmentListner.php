<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Piplin\Bus\Events\TaskFinishedEvent;
use Piplin\Bus\Jobs\SetupTaskJob;
use Piplin\Models\Task;
use Piplin\Models\EnvironmentLink;

/**
 * When a deploy finished, trigger linked environment to deploy.
 */
class DeployLinkedEnvironmentListner implements ShouldQueue
{
    use InteractsWithQueue, DispatchesJobs;

    /**
     * Handle the event.
     *
     * @param  TaskFinishedEvent $event
     * @return void
     */
    public function handle(TaskFinishedEvent $event)
    {
        $task = $event->task;

        if (!$task->isSuccessful()) {
            return;
        }

        $project = $task->project;

        $oppositeEnvironments = $task->environments->pluck('oppositePivot')->flatten();
        $ids                  = [];
        $link_type            = EnvironmentLink::MANUAL;
        foreach ($oppositeEnvironments as $item) {
            $ids[] = $item->id;

            if ($item->pivot) {
                $link_type = $item->pivot->link_type;
            }
        }

        if (sizeof($ids) < 1) {
            return;
        }

        // Fix me
        $fields = [
            'reason'         => trans('environments.link_deploy_reason'),
            'project_id'     => $project->id,
            'environments'   => $ids,
            'branch'         => $project->branch,
            'optional'       => [],
        ];

        $optional     = array_pull($fields, 'optional');
        $environments = array_pull($fields, 'environments');

        $fields['status'] = $link_type === EnvironmentLink::AUTOMATIC ? Task::PENDING : Task::DRAFT;

        $new = Task::create($fields);

        $this->dispatch(new SetupTaskJob(
            $new,
            $environments,
            $optional
        ));
    }
}
