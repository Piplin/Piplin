<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
use Piplin\Models\Project;
use Piplin\Models\User;

/**
 * Policy for projects.
 */
class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * View permission.
     *
     * @param  User    $user
     * @param  Project $project
     * @return bool
     */
    public function view(User $user, Project $project)
    {
        return $project->can('view', $user);
    }

    /**
     * Deploy permission.
     *
     * @param  User    $user
     * @param  Project $project
     * @return bool
     */
    public function deploy(User $user, Project $project)
    {
        return $project->can('view', $user);
    }

    /**
     * Update permission.
     *
     * @param  User    $user
     * @param  Project $project
     * @return bool
     */
    public function update(User $user, Project $project)
    {
        return $project->can('update', $user);
    }

    /**
     * Manage permission.
     *
     * @param  User    $user
     * @param  Project $project
     * @return bool
     */
    public function manage(User $user, Project $project)
    {
        return $project->can('manage', $user);
    }
}
