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

use Fixhub\Presenters\DeployStepPresenter;
use Illuminate\Database\Eloquent\Model;
use McCool\LaravelAutoPresenter\HasPresenter;

/**
 * The deployment step model.
 */
class DeployStep extends Model implements HasPresenter
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'            => 'integer',
        'command_id'    => 'integer',
        'deployment_id' => 'integer',
        'stage'         => 'integer',
        'optional'      => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['stage', 'deployment_id', 'command_id'];

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
            Command::DO_CLONE,
            Command::DO_INSTALL,
            Command::DO_ACTIVATE,
            Command::DO_PURGE,
        ], true));
    }

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return DeployStepPresenter::class;
    }
}
