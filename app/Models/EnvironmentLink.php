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
 *
 * @property int $id
 * @property int $link_type
 * @property int $environment_id
 * @property int $opposite_environment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Piplin\Models\Environment $environment
 * @property-read \Piplin\Models\Environment $opposite
 * @method static \Illuminate\Database\Eloquent\Builder|EnvironmentLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EnvironmentLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EnvironmentLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|EnvironmentLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnvironmentLink whereEnvironmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnvironmentLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnvironmentLink whereLinkType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnvironmentLink whereOppositeEnvironmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnvironmentLink whereUpdatedAt($value)
 * @mixin \Eloquent
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
