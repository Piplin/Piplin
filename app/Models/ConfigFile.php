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
use Piplin\Models\Traits\BroadcastChanges;
use Piplin\Models\Traits\HasTargetable;

/**
 * Static file for project.
 */
class ConfigFile extends Model
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

    const SUCCESSFUL = 0;
    const UNSYNCED   = 1;
    const FAILED     = 2;
    const SYNCING    = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'path', 'content', 'output'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Additional attributes to include in the JSON representation.
     *
     * @var array
     */
    protected $appends = ['environment_names'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'         => 'integer',
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
     * Determines whether the config file is currently being syncing.
     *
     * @return bool
     */
    public function isSyncing()
    {
        return ($this->status === self::SYNCING);
    }

    /**
     * Gets the readable list of environments.
     *
     * @return string
     */
    public function getEnvironmentNamesAttribute()
    {
        $environments = [];
        foreach ($this->environments as $environment) {
            $environments[] = $environment->name;
        }

        if (count($environments)) {
            return implode(', ', $environments);
        }

        return trans('app.none');
    }
}
