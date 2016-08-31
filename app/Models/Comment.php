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
 * Comment model.
 */
class Comment extends Model
{
    /**
     * The fillable properties.
     *
     * @var string[]
     */
    protected $fillable = ['author_id', 'commentable_id', 'commentable_type', 'description'];

    /**
     * Get all of the owning commentable models.
     *
     * @return MorphTo
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}
