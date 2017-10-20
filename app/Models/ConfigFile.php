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

/**
 * Static file for project.
 */
class ConfigFile extends Model
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'path', 'content'];

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
