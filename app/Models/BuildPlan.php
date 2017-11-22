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
 */
class BuildPlan extends Model implements HasPresenter
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

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
