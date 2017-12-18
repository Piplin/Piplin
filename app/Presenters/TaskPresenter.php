<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;
use Piplin\Models\Command;
use Piplin\Models\Task;
use Piplin\Models\BuildPlan;
use Piplin\Presenters\Traits\RuntimePresenter;

/**
 * The view presenter for a task class.
 */
class TaskPresenter extends BasePresenter
{
    use RuntimePresenter;

    /**
     * Returns the build status needed by CCTray
     * These strings can not be translated.
     *
     * @return string
     */
    public function cc_tray_status()
    {
        if ($this->wrappedObject->status === Task::COMPLETED || $this->wrappedObject->status === Task::COMPLETED_WITH_ERRORS) {
            return 'Success';
        } elseif ($this->wrappedObject->status === Task::FAILED || $this->wrappedObject->status === Task::ABORTED) {
            return 'Failure';
        }

        return 'Unknown';
    }

    /**
     * Gets the translated task status string.
     *
     * @return string
     */
    public function readable_status()
    {
        if ($this->wrappedObject->status === Task::COMPLETED) {
            return trans('tasks.completed');
        } elseif ($this->wrappedObject->status === Task::COMPLETED_WITH_ERRORS) {
            return trans('tasks.completed_with_errors');
        } elseif ($this->wrappedObject->status === Task::ABORTING) {
            return trans('tasks.aborting');
        } elseif ($this->wrappedObject->status === Task::ABORTED) {
            return trans('tasks.aborted');
        } elseif ($this->wrappedObject->status === Task::FAILED) {
            return trans('tasks.failed');
        } elseif ($this->wrappedObject->status === Task::RUNNING) {
            return trans('tasks.running');
        } elseif ($this->wrappedObject->status === Task::DRAFT) {
            return trans('tasks.draft');
        }

        return trans('tasks.pending');
    }

    /**
     * Gets the formatted task reason.
     *
     * @return string
     */
    public function formatted_reason()
    {
        return nl2br($this->wrappedObject->reason);
    }

    /**
     * Gets the IDs of the optional commands which were included in the tasks, for use in a data attribute.
     *
     * @return string
     */
    public function optional_commands_used()
    {
        return $this->wrappedObject->commands->filter(function (Command $command) {
            return $command->optional;
        })->implode('id', ',');
    }

    /**
     * Gets the CSS icon class for the task status.
     *
     * @return string
     */
    public function icon()
    {
        $finished_statuses = [Task::FAILED, Task::COMPLETED_WITH_ERRORS];

        if ($this->wrappedObject->status === Task::COMPLETED) {
            return 'check';
        } elseif (in_array($this->wrappedObject->status, $finished_statuses, true)) {
            return 'close';
        } elseif (in_array($this->wrappedObject->status, [Task::ABORTING, Task::ABORTED], true)) {
            return 'warning';
        } elseif ($this->wrappedObject->status === Task::RUNNING) {
            return 'load piplin-spin';
        } elseif ($this->wrappedObject->status === Task::DRAFT) {
            return 'edit';
        }

        return 'clock';
    }

    /**
     * Gets the CSS class for the task status.
     *
     * @return string
     */
    public function css_class()
    {
        if ($this->wrappedObject->status === Task::COMPLETED) {
            return 'success';
        } elseif (in_array($this->wrappedObject->status, [Task::FAILED, Task::COMPLETED_WITH_ERRORS], true)) {
            return 'danger';
        } elseif (in_array($this->wrappedObject->status, [Task::RUNNING, Task::ABORTING, Task::ABORTED], true)) {
            return 'warning';
        } elseif ($this->wrappedObject->status === Task::DRAFT) {
            return 'navy';
        }

        return 'info';
    }

    /**
     * Gets the CSS class for the task status for the timeline.
     *
     * @return string
     */
    public function timeline_css_class()
    {
        if ($this->wrappedObject->status === Task::COMPLETED) {
            return 'white';
        } elseif (in_array($this->wrappedObject->status, [Task::FAILED], true)) {
            return 'red';
        } elseif (in_array($this->wrappedObject->status, [Task::RUNNING, Task::ABORTING, Task::ABORTED, Task::COMPLETED_WITH_ERRORS], true)) {
            return 'yellow';
        } elseif ($this->wrappedObject->status === Task::DRAFT) {
            return 'navy';
        }

        return 'aqua';
    }

    /**
     * Gets the name of the committer, or the "Loading" string if it has not yet been determined.
     *
     * @return string
     */
    public function committer_name()
    {
        if ($this->wrappedObject->committer === Task::LOADING) {
            if ($this->wrappedObject->status === Task::FAILED) {
                return trans('tasks.unknown');
            }

            return trans('tasks.loading');
        }

        return $this->wrappedObject->committer;
    }

    /**
     * Gets the short commit hash, or the "Loading" string if it has not yet been determined.
     *
     * @return string
     */
    public function short_commit_hash()
    {
        if ($this->wrappedObject->short_commit === Task::LOADING) {
            if ($this->wrappedObject->status === Task::FAILED) {
                return trans('tasks.unknown');
            }

            return trans('tasks.loading');
        }

        return $this->wrappedObject->short_commit;
    }

    /**
     * Gets the readable list of environments.
     *
     * @return string
     */
    public function environment_names()
    {
        $environments = [];
        if ($this->wrappedObject->targetable && $this->wrappedObject->targetable instanceof BuildPlan) {
            $runContainers = $this->wrappedObject->targetable->servers;
        } else {
            $runContainers = $this->wrappedObject->environments;
        }

        foreach ($runContainers as $environment) {
            $environments[] = $environment->name;
        }

        if (count($environments)) {
            return implode(', ', $environments);
        }

        return trans('app.none');
    }
}
