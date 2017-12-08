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
use Piplin\Presenters\RuntimeInterface;
use Piplin\Presenters\ServerLogPresenter;

/**
 * Server log model.
 */
class ServerLog extends Model implements HasPresenter, RuntimeInterface
{
    const COMPLETED = 0;
    const PENDING   = 1;
    const RUNNING   = 2;
    const FAILED    = 3;
    const CANCELLED = 4;

    /**
     * The fields which should be tried as Carbon instances.
     *
     * @var array
     */
    protected $dates = ['started_at', 'finished_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['server_id', 'task_step_id', 'environment_id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'             => 'integer',
        'server_id'      => 'integer',
        'task_step_id'   => 'integer',
        'environment_id' => 'integer',
        'status'         => 'integer',
    ];

    /**
     * Belongs to assocation.
     *
     * @return Server
     */
    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Belongs to assocation.
     *
     * @return Server
     */
    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    /**
     * Calculates how long the commands were running on the server for.
     *
     * @return false|int Returns false if the command has not yet finished or the runtime in seconds
     */
    public function runtime()
    {
        if (!$this->finished_at) {
            return false;
        }

        return $this->started_at->diffInSeconds($this->finished_at);
    }

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return ServerLogPresenter::class;
    }
}
