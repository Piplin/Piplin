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
use Illuminate\Database\Eloquent\Model;

/**
 * Tip model.
 */
class Tip extends Model
{
    use BroadcastChanges;

    /**
     * The fillable properties.
     *
     * @var string[]
     */
    protected $fillable = ['body', 'status'];

    /**
     * Additional attributes to include in the JSON representation.
     *
     * @var array
     */
    protected $appends = ['excerpt'];

    /**
     * Define an accessor for the excerpt.
     *
     * @return string
     */
    public function getExcerptAttribute()
    {
        return str_limit($this->body, 50);
    }
}
