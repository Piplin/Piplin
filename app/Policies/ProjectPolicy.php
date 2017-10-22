<?php

namespace Fixhub\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Fixhub\Models\Project;
use Fixhub\Models\User;

/**
 * Policy for projects
 */
class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * View permission.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function view(User $user, Project $project)
    {
        return true;
    }

    /**
     * Update permission.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function update(User $user, Project $project)
    {
        return true;
    }

    /**
     * Deploy permission.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function deploy(User $user, Project $project)
    {
        return true;
    }

    /**
     * Manage permission.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function manage(User $user, Project $project)
    {
        return true;
    }
}
