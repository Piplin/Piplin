<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Observers;

use Piplin\Models\Release;

/**
 * Event observer for Release model.
 */
class ReleaseObserver
{
    /**
     * Called when the model is deleting.
     *
     * @param Release $release
     */
    public function creating(Release $release)
    {
        $max = Release::getMaxInternalId($release->project_id);

        $release->internal_id = $max + 1;
    }
}
