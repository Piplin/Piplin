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
use McCool\LaravelAutoPresenter\HasPresenter;
use Piplin\Models\Traits\BroadcastChanges;
use Piplin\Models\Traits\HasTargetable;
use Piplin\Presenters\BuildPlanPresenter;

/**
 * Plan model.
 *
 * @property int $id
 * @property string $name
 * @property int $project_id
 * @property int $status
 * @property string|null $last_run
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Command[] $commands
 * @property-read int|null $commands_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Pattern[] $patterns
 * @property-read int|null $patterns_count
 * @property-read \Piplin\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Server[] $servers
 * @property-read int|null $servers_count
 * @property-read Model|\Eloquent $targetable
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan newQuery()
 * @method static \Illuminate\Database\Query\Builder|BuildPlan onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan whereLastRun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BuildPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|BuildPlan withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BuildPlan withoutTrashed()
 * @mixin \Eloquent
 */
class BuildPlan extends Model implements HasPresenter
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'project_id',
    ];

    /**
     * Belongs to relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Checks ability for specified plan.
     *
     * @return bool
     */
    public function can()
    {
        return true;
    }

    /**
     * Has many relationship.
     *
     * @return Command
     */
    public function commands()
    {
        return $this->morphMany(Command::class, 'targetable')->orderBy('order', 'ASC');
    }

    /**
     * Has many relationship.
     *
     * @return Server
     */
    public function servers()
    {
        return $this->morphMany(Server::class, 'targetable');
    }

    /**
     * Has many relationship.
     *
     * @return Hook
     */
    public function patterns()
    {
        return $this->hasMany(Pattern::class)
                    ->orderBy('name');
    }

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return BuildPlanPresenter::class;
    }
}
