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
 * Model for environmental variables.
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property int $targetable_id
 * @property string $targetable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Model|\Eloquent $targetable
 * @method static \Illuminate\Database\Eloquent\Builder|Variable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Variable newQuery()
 * @method static \Illuminate\Database\Query\Builder|Variable onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Variable query()
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereTargetableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereTargetableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Variable withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Variable withoutTrashed()
 * @mixin \Eloquent
 */
class Variable extends Model
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'value'];

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
