<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Models\Traits;

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
