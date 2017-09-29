<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Models\Command;
use Fixhub\Models\Project;
use Illuminate\Http\Request;

/**
 * The controller of projects.
 */
class ProjectController extends Controller
{
    /**
     * The details of an individual project.
     *
     * @param int $project_id
     * @param string $tab
     *
     * @return View
     */
    public function show($project_id, $tab = '')
    {
        $project = Project::findOrFail($project_id);

        $optional = $project->commands->filter(function (Command $command) {
            return $command->optional;
        });

        return view('projects.show', [
            'title'           => $project->group->name.'/'.$project->name,
            'project'         => $project,
            'servers'         => $project->servers,
            'hooks'           => $project->hooks,
            'sharedFiles'     => $project->sharedFiles,
            'configFiles'     => $project->configFiles,
            'variables'       => $project->variables,
            'environments'    => $project->environments,
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id'   => $project->id,
            'optional'        => $optional,
            'tags'            => $project->tags()->reverse(),
            'branches'        => $project->branches(),
            'route'           => 'commands.step',
            'tab'             => $tab,
        ]);
    }

    /**
     * The details of an individual project with a apply dialog.
     *
     * @param int $project_id
     *
     * @return View
     */
    public function apply($project_id)
    {
        return $this->show($project_id)->withAction('apply');
    }
}
