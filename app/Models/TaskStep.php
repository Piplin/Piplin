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
use McCool\LaravelAutoPresenter\HasPresenter;
use Piplin\Presenters\TaskStepPresenter;

/**
 * The task step model.
 */
class TaskStep extends Model implements HasPresenter
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'         => 'integer',
        'command_id' => 'integer',
        'task_id'    => 'integer',
        'stage'      => 'integer',
        'optional'   => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['stage', 'task_id', 'command_id'];

    /**
     * Has many relationship.
     *
     * @return ServerLog
     */
    public function logs()
    {
        return $this->hasMany(ServerLog::class);
    }

    /**
     * Belong to relationship.
     *
     * @return Command
     */
    public function command()
    {
        return $this->belongsTo(Command::class)
                    ->withTrashed();
    }

    /**
     * Determines if the step is a BEFORE or AFTER step.
     *
     * @return bool
     */
    public function isCustom()
    {
        return (!in_array($this->stage, [
            // Deploy
            Command::DO_CLONE,
            Command::DO_INSTALL,
            Command::DO_ACTIVATE,
            Command::DO_PURGE,
            // Build
            Command::DO_PREPARE,
            Command::DO_BUILD,
            Command::DO_TEST,
            Command::DO_RESULT,
        ], true));
    }

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return TaskStepPresenter::class;
    }
}
