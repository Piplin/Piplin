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
 * Artifact model.
 *
 * @property int $id
 * @property string $file_name
 * @property int $file_size
 * @property string $mime
 * @property int $task_id
 * @property int $server_log_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Piplin\Models\ServerLog $log
 * @property-read \Piplin\Models\Task $task
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact newQuery()
 * @method static \Illuminate\Database\Query\Builder|Artifact onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact query()
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact whereServerLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Artifact withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Artifact withoutTrashed()
 * @mixin \Eloquent
 */
class Artifact extends Model
{
    use SoftDeletes, BroadcastChanges;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_name',
        'file_size',
        'mime',
        'task_id',
        'server_log_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'deleted_at',
        'updated_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'            => 'integer',
        'task_id'       => 'integer',
        'server_log_id' => 'integer',
    ];

    /**
     * Belongs to relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Belongs to relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function log()
    {
        return $this->belongsTo(ServerLog::class);
    }
}
