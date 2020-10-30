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
 * Git Ref model.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_tag
 * @property int $project_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Piplin\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|Ref newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ref newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ref query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ref whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ref whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ref whereIsTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ref whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ref whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ref whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Ref extends Model
{
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $visible = ['id', 'name', 'is_tag'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'project_id', 'is_tag'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_tag' => 'boolean',
    ];

    /**
     * Belongs to relationship.
     *
     * @return Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
