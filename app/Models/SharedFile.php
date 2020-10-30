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
 * Shared files or directories for a project.
 *
 * @property int $id
 * @property string $name
 * @property string $file
 * @property int $targetable_id
 * @property string $targetable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Model|\Eloquent $targetable
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile newQuery()
 * @method static \Illuminate\Database\Query\Builder|SharedFile onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile whereTargetableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile whereTargetableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|SharedFile withTrashed()
 * @method static \Illuminate\Database\Query\Builder|SharedFile withoutTrashed()
 * @mixin \Eloquent
 */
class SharedFile extends Model
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'file'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'         => 'integer',
    ];
}
