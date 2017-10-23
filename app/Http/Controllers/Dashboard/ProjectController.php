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

use Illuminate\Http\Request;
use Carbon\Carbon;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Models\Command;
use Fixhub\Models\Project;
use Illuminate\Support\Facades\Auth;

/**
 * The controller of projects.
 */
class ProjectController extends Controller
{
    /**
     * The details of an individual project.
     *
     * @param Project $project
     * @param string $tab
     *
     * @return View
     */
    public function show(Project $project, $tab = '')
    {
        $optional = $project->commands->filter(function (Command $command) {
            return $command->optional;
        });

        $data = [
            'project'         => $project,
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id'   => $project->id,
            'optional'        => $optional,
            'tags'            => $project->tags()->reverse(),
            'branches'        => $project->branches(),
            'tab'             => $tab,
            'title'           => trans('deployments.label'),
            'breadcrumb'      => [
                ['url' => route('projects', ['id' => $project->id]), 'label' => $project->name],
            ]
        ];

        $data['environments'] = $project->environments;
        if ($tab == 'commands') {
            $data['route'] = 'commands.step';
            $data['variables'] = $project->variables;
            $data['title'] = trans('commands.label');
        } elseif ($tab == 'config-files') {
            $data['configFiles'] = $project->configFiles;
            $data['title'] = trans('configFiles.label');
        } elseif ($tab == 'shared-files') {
            $data['sharedFiles'] = $project->sharedFiles;
            $data['title'] = trans('sharedFiles.tab_label');
        } elseif ($tab == 'hooks') {
            $data['hooks'] = $project->hooks;
        } elseif ($tab == 'members') {
            $data['members'] = $project->members->toJson();
            $data['title'] = trans('hooks.label');
        } elseif ($tab == 'environments') {
            $data['title'] = trans('environments.label');
        }

        return view('dashboard.projects.show', $data);
    }
}
