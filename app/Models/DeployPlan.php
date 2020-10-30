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
use Piplin\Models\Traits\SetupRelations;
use Piplin\Presenters\DeployPlanPresenter;

/**
 * Deploy plan model.
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\ConfigFile[] $configFiles
 * @property-read int|null $config_files_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Environment[] $environments
 * @property-read int|null $environments_count
 * @property-read \Piplin\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\SharedFile[] $sharedFiles
 * @property-read int|null $shared_files_count
 * @property-read Model|\Eloquent $targetable
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Variable[] $variables
 * @property-read int|null $variables_count
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan newQuery()
 * @method static \Illuminate\Database\Query\Builder|DeployPlan onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan whereLastRun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeployPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|DeployPlan withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DeployPlan withoutTrashed()
 * @mixin \Eloquent
 */
class DeployPlan extends Model implements HasPresenter
{
    use SoftDeletes, BroadcastChanges, SetupRelations, HasTargetable;

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
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return DeployPlanPresenter::class;
    }
}
