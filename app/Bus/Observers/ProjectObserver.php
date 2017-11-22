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

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Str;
use Piplin\Bus\Jobs\GenerateKeyJob;
use Piplin\Bus\Jobs\PurgeProjectJob;
use Piplin\Bus\Jobs\Repository\UpdateGitMirrorJob;
use Piplin\Models\Project;

/**
 * Event observer for Project model.
 */
class ProjectObserver
{
    use DispatchesJobs;

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

        if (!$project->key_id) {
            $this->dispatch(new GenerateKeyJob($project));
        }
    }

    /**
     * Called when the model is updated.
     *
     * @param Project $project
     */
    public function updated(Project $project)
    {
        $repoChanged = $project->isDirty('repository');

        if ($repoChanged) {
            $this->dispatch(new UpdateGitMirrorJob($project));
        }
    }

    /**
     * Called when the model is deleting.
     *
     * @param Project $project
     */
    public function deleting(Project $project)
    {
        if ($project->trashed()) {
            return;
        }

        $this->dispatch(new PurgeProjectJob($project));
    }
}
