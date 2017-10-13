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

use McCool\LaravelAutoPresenter\BasePresenter;

/**
 * The view presenter for a revision class.
 */
class RevisionPresenter extends BasePresenter
{
    /**
     * Get the creator.
     *
     * @return string
     */
    public function creator()
    {
        if ($this->wrappedObject->user) {
            return $this->wrappedObject->user->name;
        }

        return trans('revisions.system');
    }

    /**
     * Get the item details.
     *
     * @return string
     */
    public function item_details()
    {
        $pre = $post = $key = '';

        switch ($this->wrappedObject->revisionable_type) {
            case 'Fixhub\\Models\\ProjectGroup':
                $pre = trans('revisions.group');
                break;
            case 'Fixhub\\Models\\Project':
                $pre = trans('revisions.project');
                break;
            case 'Fixhub\\Models\\DeployTemplate':
                $pre = trans('revisions.template');
                break;
            case 'Fixhub\\Models\\Key':
                $pre = trans('revisions.key');
                break;
            case 'Fixhub\\Models\\Hook':
                $pre = trans('revisions.hook');
                break;
            case 'Fixhub\\Models\\Environment':
                $pre = trans('revisions.environment');
                break;
            case 'Fixhub\\Models\\Server':
                $pre = trans('revisions.server');
                break;
            case 'Fixhub\\Models\\Deployment':
                $pre = trans('revisions.deployment');
                $key = $this->wrappedObject->revisionable->reason;
                break;
            case 'Fixhub\\Models\\User':
                $pre = trans('revisions.user');
                break;
            default:
                break;
        }

        if (!$this->wrappedObject->revisionable) {
            $post = trans('revisions.removed');
        } else {
            $key = $this->wrappedObject->revisionable->name;
            $post = '('.$this->wrappedObject->revisionable->id.')';
        }

        return $pre .' : '. $key . $post;
    }
}
