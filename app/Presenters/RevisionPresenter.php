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
    public function creator()
    {
        if ($this->wrappedObject->user) {
            return $this->wrappedObject->user->name;
        }

        return trans('revisions.system');
    }

    /**
     * Gets the translated revision changed field string.
     *
     * @return string
     */
    public function changed_field()
    {
        $pre = '';
        switch ($this->wrappedObject->revisionable_type) {
            case 'Fixhub\\Models\\ProjectGroup':
                $pre = trans('revisions.group').' : ' . $this->wrappedObject->revisionable->name;
                break;
            case 'Fixhub\\Models\\Project':
                $pre = trans('revisions.project').' : ' . $this->wrappedObject->revisionable->name;
                break;
            case 'Fixhub\\Models\\DeployTemplate':
                $pre = trans('revisions.template').' : ' . $this->wrappedObject->revisionable->name;
                break;
            case 'Fixhub\\Models\\Key':
                $pre = trans('revisions.key').' : ' . $this->wrappedObject->revisionable->name;
                break;
            case 'Fixhub\\Models\\Hook':
                $pre = trans('revisions.hook').' : ' . $this->wrappedObject->revisionable->name;
                break;
            case 'Fixhub\\Models\\Environment':
                $pre = trans('revisions.environment').' : ' . $this->wrappedObject->revisionable->name;
                break;
            case 'Fixhub\\Models\\Server':
                $pre = trans('revisions.server').' : ' . $this->wrappedObject->revisionable->name;
                break;
            case 'Fixhub\\Models\\Deployment':
                $pre = trans('revisions.deployment').' : ' . $this->wrappedObject->revisionable->reason;
                break;
            case 'Fixhub\\Models\\User':
                $pre = trans('revisions.user').' : ' . $this->wrappedObject->revisionable->name;
                break;
            default:
                break;
        }

        return $pre . '('.$this->wrappedObject->revisionable->id.')'. "<br />\n". $this->wrappedObject->key;
    }
}
