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
 * @property int $project_id
 * @property int $task_id
 * @property int $internal_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read string $artifact_names
 * @property-read \Piplin\Models\Project $project
 * @property-read \Piplin\Models\Task $task
 * @method static \Illuminate\Database\Eloquent\Builder|Release newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Release newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Release query()
 * @method static \Illuminate\Database\Eloquent\Builder|Release whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Release whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Release whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Release whereInternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Release whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Release whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Release whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Release whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Release extends Model
{
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $visible = ['id', 'name', 'task_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'project_id', 'task_id', 'internal_id'];

    /**
     * Finds a max internal id by the project id.
     *
     * @param int   $projectId
     *
     * @return int
     */
    public static function getMaxInternalId($projectId)
    {
        return static::where('project_id', $projectId)->max('internal_id');
    }
    /**
     * Belongs to relationship.
     *
     * @return Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Belongs to relationship.
     *
     * @return Task
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Gets the readable list of artifacts.
     *
     * @return string
     */
    public function getArtifactNamesAttribute()
    {
        return implode(' ', $this->task->artifacts->pluck('file_name')->toArray());
    }
}
