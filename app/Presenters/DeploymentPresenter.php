<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Presenters;

use Fixhub\Models\Command;
use Fixhub\Models\Deployment;
use Fixhub\Presenters\Traits\RuntimePresenter;
use McCool\LaravelAutoPresenter\BasePresenter;

/**
 * The view presenter for a deployment class.
 */
class DeploymentPresenter extends BasePresenter
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
        if ($this->wrappedObject->status === Deployment::COMPLETED || $this->wrappedObject->status === Deployment::COMPLETED_WITH_ERRORS) {
            return 'Success';
        } elseif ($this->wrappedObject->status === Deployment::FAILED || $this->wrappedObject->status === Deployment::ABORTED) {
            return 'Failure';
        }

        return 'Unknown';
    }

    /**
     * Gets the translated deployment status string.
     *
     * @return string
     */
    public function readable_status()
    {
        if ($this->wrappedObject->status === Deployment::COMPLETED) {
            return trans('deployments.completed');
        } elseif ($this->wrappedObject->status === Deployment::COMPLETED_WITH_ERRORS) {
            return trans('deployments.completed_with_errors');
        } elseif ($this->wrappedObject->status === Deployment::ABORTING) {
            return trans('deployments.aborting');
        } elseif ($this->wrappedObject->status === Deployment::ABORTED) {
            return trans('deployments.aborted');
        } elseif ($this->wrappedObject->status === Deployment::FAILED) {
            return trans('deployments.failed');
        } elseif ($this->wrappedObject->status === Deployment::DEPLOYING) {
            return trans('deployments.deploying');
        }

        return trans('deployments.pending');
    }

    /**
     * Gets the IDs of the optional commands which were included in the deployments, for use in a data attribute.
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
     * Gets the CSS icon class for the deployment status.
     *
     * @return string
     */
    public function icon()
    {
        $finished_statuses = [Deployment::FAILED, Deployment::COMPLETED_WITH_ERRORS,
                              Deployment::ABORTING, Deployment::ABORTED, ];

        if ($this->wrappedObject->status === Deployment::COMPLETED) {
            return 'checkmark-round';
        } elseif (in_array($this->wrappedObject->status, $finished_statuses, true)) {
            return 'close-round';
        } elseif ($this->wrappedObject->status === Deployment::DEPLOYING) {
            return 'load-c fixhub-spin';
        }

        return 'clock';
    }

    /**
     * Gets the CSS class for the deployment status.
     *
     * @return string
     */
    public function css_class()
    {
        if ($this->wrappedObject->status === Deployment::COMPLETED || $this->wrappedObject->status === Deployment::COMPLETED_WITH_ERRORS) {
            return 'success';
        } elseif (in_array($this->wrappedObject->status, [Deployment::FAILED, Deployment::ABORTING, Deployment::ABORTED], true)) {
            return 'danger';
        } elseif ($this->wrappedObject->status === Deployment::DEPLOYING) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Gets the CSS class for the deployment status for the timeline.
     *
     * @return string
     */
    public function timeline_css_class()
    {
        if ($this->wrappedObject->status === Deployment::COMPLETED || $this->wrappedObject->status === Deployment::COMPLETED_WITH_ERRORS) {
            return 'green';
        } elseif (in_array($this->wrappedObject->status, [Deployment::FAILED, Deployment::ABORTING, Deployment::ABORTED], true)) {
            return 'red';
        } elseif ($this->wrappedObject->status === Deployment::DEPLOYING) {
            return 'yellow';
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
        if ($this->wrappedObject->committer === Deployment::LOADING) {
            if ($this->wrappedObject->status === Deployment::FAILED) {
                return trans('deployments.unknown');
            }

            return trans('deployments.loading');
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
        if ($this->wrappedObject->short_commit === Deployment::LOADING) {
            if ($this->wrappedObject->status === Deployment::FAILED) {
                return trans('deployments.unknown');
            }

            return trans('deployments.loading');
        }

        return $this->wrappedObject->short_commit;
    }
}
