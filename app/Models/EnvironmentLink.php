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

use Illuminate\Database\Eloquent\Model;

/**
 * Model for environment links.
 */
class EnvironmentLink extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['link_id', 'environment_id', 'opposite_environment_id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'link_id'                 => 'integer',
        'environment_id'          => 'integer',
        'opposite_environment_id' => 'integer',
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
