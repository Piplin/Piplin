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
use Fixhub\Bus\Jobs\AbortDeployment;
use Fixhub\Bus\Jobs\QueueDeployment;
use Fixhub\Models\Command;
use Fixhub\Models\Deployment;
use Fixhub\Models\Project;
use Fixhub\Models\ServerLog;
use Illuminate\Http\Request;

/**
 * The controller of projects.
 */
class ProjectController extends Controller
{
    /**
     * The details of an individual project.
     *
     * @param  int  $project_id
     * @return View
     */
    public function show($project_id)
    {
        $project = Project::findOrFail($project_id);

        $optional = $project->commands->filter(function (Command $command) {
            return $command->optional;
        });

        return view('projects.show', [
            'title'           => $project->name,
            'project'         => $project,
            'servers'         => $project->servers,
            'notifySlacks'    => $project->notifySlacks,
            'notifyEmails'    => $project->notifyEmails,
            'heartbeats'      => $project->heartbeats,
            'sharedFiles'     => $project->sharedFiles,
            'configFiles'     => $project->configFiles,
            'checkUrls'       => $project->checkUrls,
            'variables'       => $project->variables,
            'targetable_type' => 'Fixhub\\Models\\Project',
            'targetable_id'   => $project->id,
            'optional'        => $optional,
            'tags'            => $project->tags()->reverse(),
            'branches'        => $project->branches(),
            'issues'          => $project->issues,
            'route'           => 'commands.step',
        ]);
    }

    /**
     * The details of an individual project with a apply dialog.
     *
     * @param  int  $project_id
     * @return View
     */
    public function apply($project_id)
    {
        return $this->show($project_id)->withAction('apply');
    }

    /**
     * Adds a deployment for the specified project to the queue.
     *
     * @param  Request  $request
     * @param  int      $project
     * @return Response
     */
    public function deploy(Request $request, $project_id)
    {
        $project = Project::findOrFail($project_id);

        if ($project->servers->where('deploy_code', true)->count() === 0) {
            return redirect()->route('projects', ['id' => $project->id]);
        }

        $data = [
            'reason'     => $request->get('reason'),
            'project_id' => $project->id,
            'branch'     => $project->branch,
            'optional'   => [],
        ];

        // If allow other branches is set, check for post data
        if ($project->allow_other_branch) {
            if ($request->has('source') && $request->has('source_' . $request->get('source'))) {
                $data['branch'] = $request->get('source_' . $request->get('source'));

                if($request->get('source') == 'commit') {
                    $data['commit'] = $data['branch'];
                    $data['branch'] = $project->branch;
                }
            }
        }

        // Get the optional commands and typecast to integers
        if ($request->has('optional') && is_array($request->get('optional'))) {
            $data['optional'] = array_filter(array_map(function ($value) {
                return filter_var($value, FILTER_VALIDATE_INT);
            }, $request->get('optional')));
        }

        $deployment = $this->createDeployment($data);

        return redirect()->route('deployments', [
            'id' => $deployment->id,
        ]);
    }

    /**
     * Creates a new instance of the server.
     *
     * @param  array $fields
     * @return Model
     */
    private function createDeployment(array $fields)
    {
        $optional = [];
        if (array_key_exists('optional', $fields)) {
            $optional = $fields['optional'];
            unset($fields['optional']);
        }

        $deployment = Deployment::create($fields);

        dispatch(new QueueDeployment(
            $deployment,
            $optional
        ));

        return $deployment;
    }
}
