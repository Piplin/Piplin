<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Models;

use Fixhub\Models\Traits\BroadcastChanges;
use Fixhub\Models\Traits\HasTargetable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use McCool\LaravelAutoPresenter\HasPresenter;
use Fixhub\Presenters\CommandPresenter;

/**
 * The command model.
 */
class Command extends Model implements HasPresenter
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

    // For deploy
    const BEFORE_CLONE    = 1;
    const DO_CLONE        = 2;
    const AFTER_CLONE     = 3;
    const BEFORE_INSTALL  = 4;
    const DO_INSTALL      = 5;
    const AFTER_INSTALL   = 6;
    const BEFORE_ACTIVATE = 7;
    const DO_ACTIVATE     = 8;
    const AFTER_ACTIVATE  = 9;
    const BEFORE_PURGE    = 10;
    const DO_PURGE        = 11;
    const AFTER_PURGE     = 12;

    // For build
    const BEFORE_CREATE   = 31;
    const DO_CREATE       = 32;
    const AFTER_CREATE    = 33;
    const BEFORE_TEST     = 34;
    const DO_TEST         = 35;
    const AFTER_TEST      = 36;
    const BEFORE_BUILD    = 37;
    const DO_BUILD        = 38;
    const AFTER_BUILD     = 39;
    const BEFORE_FINISH   = 40;
    const DO_FINISH       = 41;
    const AFTER_FINISH    = 42;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'deleted_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'user', 'script', 'step', 'order', 'optional', 'default_on'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'         => 'integer',
        'step'       => 'integer',
        'optional'   => 'boolean',
        'default_on' => 'boolean',
        'order'      => 'integer',
    ];

    /**
     * Belongs to many relationship.
     *
     * @return Server
     */
    public function environments()
    {
        return $this->belongsToMany(Environment::class)
                    ->orderBy('order', 'ASC');
    }

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return CommandPresenter::class;
    }
}
