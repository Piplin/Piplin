<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Observers;

use Fixhub\Models\Project;
use Illuminate\Support\Str;

/**
 * Event observer for Project model.
 */
class ProjectObserver
{
    /**
     * Called when the model is being created.
     *
     * @param Project $project
     */
    public function creating(Project $project)
    {
        if (!$project->hash) {
            $project->hash = Str::random(60);
        }
    }
}
