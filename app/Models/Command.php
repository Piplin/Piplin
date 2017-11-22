<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use McCool\LaravelAutoPresenter\HasPresenter;
use Piplin\Models\Traits\BroadcastChanges;
use Piplin\Models\Traits\HasTargetable;
use Piplin\Presenters\CommandPresenter;

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
    const BEFORE_PREPARE = 31;
    const DO_PREPARE     = 32;
    const AFTER_PREPARE  = 33;
    const BEFORE_BUILD   = 34;
    const DO_BUILD       = 35;
    const AFTER_BUILD    = 36;
    const BEFORE_TEST    = 37;
    const DO_TEST        = 38;
    const AFTER_TEST     = 39;
    const BEFORE_RESULT  = 40;
    const DO_RESULT      = 41;
    const AFTER_RESULT   = 42;

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
     * Belongs to many relationship.
     *
     * @return Server
     */
    public function patterns()
    {
        return $this->belongsToMany(Pattern::class)
                    ->orderBy('name', 'ASC');
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
