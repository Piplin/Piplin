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
use Piplin\Models\Project;

/**
 * The view presenter for a project class.
 */
class ProjectPresenter extends CommandPresenter
{
    /**
     * Returns the build status needed by CCTray
     * These strings can not be translated.
     *
     * @return string
     */
    public function cc_tray_status()
    {
        if ($this->wrappedObject->status === Project::FINISHED || $this->wrappedObject->status === Project::FAILED) {
            return 'Sleeping';
        } elseif ($this->wrappedObject->status === Project::RUNNING) {
            return 'Building';
        } elseif ($this->wrappedObject->status === Project::PENDING) {
            return 'Pending';
        }

        return 'Unknown';
    }

    /**
     * Gets the translated project status string.
     *
     * @return string
     */
    public function readable_status()
    {
        if ($this->wrappedObject->status === Project::FINISHED) {
            return trans('projects.finished');
        } elseif ($this->wrappedObject->status === Project::RUNNING) {
            return trans('projects.running');
        } elseif ($this->wrappedObject->status === Project::FAILED) {
            return trans('projects.failed');
        } elseif ($this->wrappedObject->status === Project::PENDING) {
            return trans('projects.pending');
        }

        return trans('projects.not_deployed');
    }

    /**
     * Gets the CSS icon class for the project status.
     *
     * @return string
     */
    public function icon()
    {
        if ($this->wrappedObject->status === Project::FINISHED) {
            return 'check';
        } elseif ($this->wrappedObject->status === Project::RUNNING) {
            return 'load piplin-spin';
        } elseif ($this->wrappedObject->status === Project::FAILED) {
            return 'close';
        } elseif ($this->wrappedObject->status === Project::PENDING) {
            return 'clock';
        }

        return 'help';
    }

    /**
     * Gets the CSS class for the project status.
     *
     * @return string
     */
    public function css_class()
    {
        if ($this->wrappedObject->status === Project::FINISHED) {
            return 'success';
        } elseif ($this->wrappedObject->status === Project::RUNNING) {
            return 'warning';
        } elseif ($this->wrappedObject->status === Project::FAILED) {
            return 'danger';
        } elseif ($this->wrappedObject->status === Project::PENDING) {
            return 'info';
        }

        return 'default';
    }

    /**
     * Gets an icon which represents the repository type.
     *
     * @return string
     */
    public function type_icon()
    {
        $details = $this->accessDetails();

        if (isset($details['domain'])) {
            if (preg_match('/github\.com/', $details['domain'])) {
                return 'piplin-github';
            } elseif (preg_match('/gitlab\.com/', $details['domain'])) {
                return 'piplin-gitlab';
            } elseif (preg_match('/bitbucket/', $details['domain'])) {
                return 'piplin-bitbucket';
            } elseif (preg_match('/amazonaws\.com/', $details['domain'])) {
                return 'piplin-amazon';
            }
        }

        return 'piplin-cube';
    }
}
