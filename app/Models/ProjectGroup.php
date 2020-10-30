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

/**
 * Project Group model.
 *
 * @property int $id
 * @property string $name
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read int $project_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Project[] $projects
 * @property-read int|null $projects_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectGroup newQuery()
 * @method static \Illuminate\Database\Query\Builder|ProjectGroup onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectGroup whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|ProjectGroup withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ProjectGroup withoutTrashed()
 * @mixin \Eloquent
 */
class ProjectGroup extends Model
{
    use SoftDeletes, BroadcastChanges;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'order'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'deleted_at', 'updated_at'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Additional attributes to include in the JSON representation.
     *
     * @var array
     */
    protected $appends = ['project_count'];

    /**
     * Has many relationship.
     *
     * @return Project
     */
    public function projects()
    {
        return $this->morphMany(Project::class, 'targetable');
    }

    /**
     * Define a accessor for the count of projects.
     *
     * @return int
     */
    public function getProjectCountAttribute()
    {
        return $this->projects->count();
    }
}
