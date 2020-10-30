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
 * Pattern model.
 *
 * @property int $id
 * @property string $name
 * @property string $copy_pattern
 * @property int $build_plan_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Piplin\Models\BuildPlan $buildPlan
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Command[] $commands
 * @property-read int|null $commands_count
 * @property-read string $command_names
 * @method static \Illuminate\Database\Eloquent\Builder|Pattern newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pattern newQuery()
 * @method static \Illuminate\Database\Query\Builder|Pattern onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Pattern query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pattern whereBuildPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pattern whereCopyPattern($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pattern whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pattern whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pattern whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pattern whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pattern whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Pattern withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Pattern withoutTrashed()
 * @mixin \Eloquent
 */
class Pattern extends Model
{
    use SoftDeletes, BroadcastChanges;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'copy_pattern', 'build_plan_id'];

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
    protected $appends = ['command_names'];

    /**
     * Belongs to relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function buildPlan()
    {
        return $this->belongsTo(BuildPlan::class);
    }

    /**
     * Belongs to many relationship.
     *
     * @return Command
     */
    public function commands()
    {
        return $this->belongsToMany(Command::class);
    }

    /**
     * Gets the readable list of commands.
     *
     * @return string
     */
    public function getCommandNamesAttribute()
    {
        $commands = [];
        foreach ($this->commands as $command) {
            $commands[] = $command->name;
        }

        if (count($commands)) {
            return implode(', ', $commands);
        }

        return trans('app.none');
    }
}
