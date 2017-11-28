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

/**
 * Model for environment links.
 */
class EnvironmentLink extends Model
{
    const AUTOMATIC = 1;
    const MANUAL    = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['link_type', 'environment_id', 'opposite_environment_id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'link_type'                 => 'integer',
        'environment_id'            => 'integer',
        'opposite_environment_id'   => 'integer',
    ];

    /**
     * Belongs to relationship.
     *
     * @return Group
     */
    public function environment()
    {
        return $this->belongsTo(Environment::class, 'environment_id', 'id');
    }

    /**
     * Belongs to relationship.
     *
     * @return Group
     */
    public function opposite()
    {
        return $this->belongsTo(Environment::class, 'opposite_environment_id', 'id');
    }
}
