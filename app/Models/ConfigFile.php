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
 * Static file for project.
 *
 * @property int $id
 * @property string $name
 * @property string $path
 * @property string $content
 * @property int $targetable_id
 * @property string $targetable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $status
 * @property string|null $last_run
 * @property string|null $output
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Environment[] $environments
 * @property-read int|null $environments_count
 * @property-read string $environment_names
 * @property-read Model|\Eloquent $targetable
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile newQuery()
 * @method static \Illuminate\Database\Query\Builder|ConfigFile onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereLastRun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereOutput($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereTargetableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereTargetableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|ConfigFile withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ConfigFile withoutTrashed()
 * @mixin \Eloquent
 */
class ConfigFile extends Model
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

    const SUCCESSFUL = 0;
    const UNSYNCED   = 1;
    const FAILED     = 2;
    const SYNCING    = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'path', 'content', 'output'];

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
     * Determines whether the config file is currently being syncing.
     *
     * @return bool
     */
    public function isSyncing()
    {
        return ($this->status === self::SYNCING);
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
