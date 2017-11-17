<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Models;

use Fixhub\Models\Traits\BroadcastChanges;
use Fixhub\Models\Traits\HasTargetable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use McCool\LaravelAutoPresenter\HasPresenter;
use Fixhub\Presenters\PlanPresenter;

/**
 * Plan model.
 */
class Plan extends Model implements HasPresenter
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
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return PlanPresenter::class;
    }
}
