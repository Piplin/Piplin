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

use Fixhub\Http\Controllers\Controller;
use Fixhub\Bus\Jobs\AbortDeployment;
use Fixhub\Bus\Jobs\QueueDeployment;
use Fixhub\Models\Command;
use Fixhub\Models\Deployment;
use Fixhub\Models\Project;
use Fixhub\Models\ServerLog;
use Illuminate\Http\Request;
use McCool\LaravelAutoPresenter\Facades\AutoPresenter;

/**
 * The controller for showing the status of deployments.
 */
class DeploymentController extends Controller
{
    /**
     * Show the deployment details.
     *
     * @param  int      $deployment
     * @return Response
     */
    public function show($deployment_id)
    {
        $deployment = Deployment::findOrFail($deployment_id);

        $output = [];
        foreach ($deployment->steps as $step) {
            foreach ($step->servers as $server) {
                $server->server;

                $server->runtime = ($server->runtime() === false ?
                        null : AutoPresenter::decorate($server)->readable_runtime);
                $server->output  = ((is_null($server->output) || !strlen($server->output)) ? null : '');

                $output[] = $server;
            }
        }

        $project = $deployment->project;

        return view('deployment.show', [
            'breadcrumb' => [
                ['url' => route('projects', ['id' => $project->id]), 'label' => $project->name],
            ],
            'title'      => trans('deployments.deployment_number', ['id' => $deployment->id]),
            'subtitle'   => $project->name,
            'project'    => $project,
            'deployment' => $deployment,
            'output'     => json_encode($output), // PresentableInterface does not correctly json encode the models
        ]);
    }

    /**
     * Loads a previous deployment and then creates a new deployment based on it.
     *
     * @param  Request  $request
     * @param  int      $previous_id
     * @return Response
     */
    public function rollback(Request $request, $previous_id)
    {
        $optional = [];

        // Get the optional commands and typecast to integers
        if ($request->has('optional') && is_array($request->get('optional'))) {
            $optional = array_filter(array_map(function ($value) {
                return filter_var($value, FILTER_VALIDATE_INT);
            }, $request->get('optional')));
        }

        $previous = Deployment::findOrFail($previous_id);

        $fileds = [
            'committer'       => $previous->committer,
            'committer_email' => $previous->committer_email,
            'commit'          => $previous->commit,
            'project_id'      => $previous->project_id,
            'branch'          => $previous->branch,
            'project_id'      => $previous->project_id,
            'optional'        => $optional,
        ];

        $deployment = $this->createDeployment($fileds);

        return redirect()->route('deployments', [
            'id' => $deployment->id,
        ]);
    }

    /**
     * Abort a deployment.
     *
     * @param  int      $deployment_id
     * @return Response
     */
    public function abort($deployment_id)
    {
        $deployment = Deployment::findOrFail($deployment_id);

        if (!$deployment->isAborting()) {
            $deployment->status = Deployment::ABORTING;
            $deployment->save();

            dispatch(new AbortDeployment($deployment));
        }

        return redirect()->route('deployments', [
            'id' => $deployment_id,
        ]);
    }

    /**
     * Gets the log output of a particular deployment step.
     *
     * @param  int $log_id
     * @return ServerLog
     */
    public function log($log_id)
    {
        $log = ServerLog::findOrFail($log_id);
        $log->runtime = ($log->runtime() === false ? null : AutoPresenter::decorate($log)->readable_runtime);

        return $log;
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
            $deployment->project,
            $deployment,
            $optional
        ));

        return $deployment;
    }
}
