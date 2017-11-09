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

/**
 * The build command model.
 */
class BuildCommand extends Model implements HasPresenter
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

    const BEFORE_INSTALL = 1;
    const DO_INSTALL     = 2;
    const BEFORE_SCRIPT  = 3;
    const DO_SCRIPT      = 4;
    const AFTER_SCRIPT   = 5;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'deleted_at', 'updated_at'];
}
