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
use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreDeploymentRequest;
use Fixhub\Bus\Jobs\AbortDeploymentJob;
use Fixhub\Bus\Jobs\ApproveDeploymentJob;
use Fixhub\Bus\Jobs\SetupDeploymentJob;
use Fixhub\Models\Command;
use Fixhub\Models\Deployment;
use Fixhub\Models\Project;
use McCool\LaravelAutoPresenter\Facades\AutoPresenter;

/**
 * The controller for showing the status of deployments.
 */
class DeploymentController extends Controller
{
    /**
     * Show the deployment details.
     *
     * @param int $deployment_id
     *
     * @return Response
     */
    public function show($deployment_id)
    {
        $deployment = Deployment::findOrFail($deployment_id);

        $output = [];
        foreach ($deployment->steps as $step) {
            foreach ($step->logs as $log) {
                if ($log->server) {
                    $log->server->environment_name = $log->server->environment ? $log->server->environment->name : null;
                }

                $log->runtime = ($log->runtime() === false ?
                        null : AutoPresenter::decorate($log)->readable_runtime);
                $log->output  = ((is_null($log->output) || !strlen($log->output)) ? null : '');

                $output[] = $log;
            }
        }

        $data = [
            'title'      => trans('deployments.deployment_number', ['id' => $deployment->id]),
            'deployment' => $deployment,
            'output'     => json_encode($output), // PresentableInterface does not correctly json encode the models
        ];

        $project = $deployment->project ?: null;
        if ($project) {
            $data['breadcrumb'] = [
                ['url' => route('projects', ['id' => $project->id]), 'label' => $project->name],
            ];
            $data['project'] = $project;
            $data['subtitle'] = '('.$deployment->short_commit . ' - ' . $deployment->branch.')';
        }

        return view('dashboard.deployments.show', $data);
    }

    /**
     * Adds a deployment for the specified project to the queue.
     *
     * @param StoreDeploymentRequest $request
     * @param int $project_id
     *
     * @return Response
     */
    public function create(StoreDeploymentRequest $request, $project_id)
    {
        // Fix me! see also in DeploymentController and IncomingWebhookController
        $project = Project::findOrFail($project_id);

        if ($project->environments->count() === 0) {
            return redirect()->route('projects', ['id' => $project->id]);
        }

        $fields = [
            'reason'         => $request->get('reason'),
            'project_id'     => $project->id,
            'environments'   => $request->get('environments'),
            'branch'         => $project->branch,
            'optional'       => [],
        ];

        // If allow other branches is set, check for post data
        if ($project->allow_other_branch) {
            if ($request->has('source') && $request->has('source_' . $request->get('source'))) {
                $fields['branch'] = $request->get('source_' . $request->get('source'));

                if ($request->get('source') == 'commit') {
                    $fields['commit'] = $fields['branch'];
                    $fields['branch'] = $project->branch;
                }
            }
        }

        // Get the optional commands and typecast to integers
        if ($request->has('optional') && is_array($request->get('optional'))) {
            $fields['optional'] = array_filter(array_map(function ($value) {
                return filter_var($value, FILTER_VALIDATE_INT);
            }, $request->get('optional')));
        }

        $optional = array_pull($fields, 'optional');
        $environments = array_pull($fields, 'environments');

        $deployment = Deployment::create($fields);

        dispatch(new SetupDeploymentJob(
            $deployment,
            $environments,
            $optional
        ));

        return redirect()->route('deployments', [
            'id' => $deployment->id,
        ]);
    }

    /**
     * Loads a previous deployment and then creates a new deployment based on it.
     *
     * @param Request $request
     * @param int     $previous_id
     *
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

        $fields = [
            'committer'       => $previous->committer,
            'committer_email' => $previous->committer_email,
            'commit'          => $previous->commit,
            'project_id'      => $previous->project_id,
            'branch'          => $previous->branch,
            'reason'          => trans('deployments.rollback_reason', [
                    'reason'  => $request->get('reason'),
                    'id'      => $previous_id,
                    'commit'  => $previous->short_commit
            ]),
        ];

        $environments = $previous->environments->pluck('id')->toArray();

        $deployment = Deployment::create($fields);

        dispatch(new SetupDeploymentJob(
            $deployment,
            $environments,
            $optional
        ));

        return redirect()->route('deployments', [
            'id' => $deployment->id,
        ]);
    }

    /**
     * Abort a deployment.
     *
     * @param int $deployment_id
     *
     * @return Response
     */
    public function abort($deployment_id)
    {
        $deployment = Deployment::findOrFail($deployment_id);

        if (!$deployment->isAborting()) {
            $deployment->status = Deployment::ABORTING;
            $deployment->save();

            dispatch(new AbortDeploymentJob($deployment));
        }

        return redirect()->route('deployments', [
            'id' => $deployment_id,
        ]);
    }

    /**
     * Approve a deployment.
     *
     * @param int $deployment_id
     *
     * @return Response
     */
    public function approve($deployment_id)
    {
        $deployment = Deployment::findOrFail($deployment_id);

        if (!$deployment->isApproved()) {
            $deployment->status = Deployment::APPROVED;
            $deployment->save();
        }

        return redirect()->route('deployments', [
            'id' => $deployment->id,
        ]);
    }

    /**
     * Deploy a deployment.
     *
     * @param int $deployment_id
     *
     * @return Response
     */
    public function deploy($deployment_id)
    {
        $deployment = Deployment::findOrFail($deployment_id);

        if ($deployment->isApproved()) {
            dispatch(new ApproveDeploymentJob($deployment));
        }

        return redirect()->route('deployments', [
            'id' => $deployment_id,
        ]);
    }
}
