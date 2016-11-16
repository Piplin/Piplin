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

use Illuminate\Database\Eloquent\Model;

/**
 * Identity model.
 */
class Identity extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'extern_uid',
        'name',
        'nickname',
        'email',
        'user_id',
        'provider_id',
    ];
}
