<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Models\Traits;

/**
 * A trait to has target.
 */
trait HasTargetable
{
    /**
     * Get all of the owning assignable models.
     */
    public function targetable()
    {
        return $this->morphTo();
    }
}
