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
use Illuminate\Support\Facades\Auth;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreDeploymentRequest;
use Fixhub\Bus\Jobs\AbortDeploymentJob;
use Fixhub\Bus\Jobs\DeployDraftJob;
use Fixhub\Bus\Jobs\CreateDeploymentJob;
use Fixhub\Models\Command;
use Fixhub\Models\Deployment;
use Fixhub\Models\Project;
use Fixhub\Models\Environment;
use McCool\LaravelAutoPresenter\Facades\AutoPresenter;

/**
 * The controller for showing the status of deployments.
 */
class DeploymentController extends Controller
{
    /**
     * Show the deployment details.
     *
     * @param Deployment $deployment
     *
     * @return Response
     */
    public function show(Deployment $deployment)
    {
        $this->authorize('view', $deployment->project);

        $output = [];
        $envLocks = [];
        foreach ($deployment->steps as $step) {
            foreach ($step->logs as $log) {
                $log->cabinet = false;
                $log->environment_name = null;
                if ($log->server && $log->environment) {
                    if (!$log->server->targetable instanceof Environment) {
                        $log->cabinet = true;
                    }

                    if (!isset($envLocks[$step->id.'_'.$log->environment_id])) {
                        $log->environment_name = $log->environment->name;
                        $envLocks[$step->id.'_'.$log->environment_id] = true;
                    }
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
     *
     * @return Response
     */
    public function create(StoreDeploymentRequest $request)
    {
        $project = Project::findOrFail($request->get('project_id'));

        $this->authorize('deploy', $project);

        $fields = [
            'reason'          => $request->get('reason'),
            'project_id'      => $project->id,
            'targetable_type' => $request->get('targetable_type'),
            'targetable_id'   => $request->get('targetable_id'),
            'project_id'      => $project->id,
            'environments'    => $request->get('environments'),
            'branch'          => $project->branch,
            'optional'        => [],
        ];

        /*
        if ($project->environments->count() === 0) {
            return [
                'success' => false,
            ];
        }
        */

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
        $fields['user_id'] = Auth::user()->id;

        dispatch(new CreateDeploymentJob($project, $fields));

        return [
            'success' => true,
        ];
    }

    /**
     * Loads a previous deployment and then creates a new deployment based on it.
     *
     * @param Request $request
     * @param Deployment $previous
     *
     * @return Response
     */
    public function rollback(Request $request, Deployment $previous)
    {
        $this->authorize('deploy', $previous->project);

        $optional = [];
        // Get the optional commands and typecast to integers
        if ($request->has('optional') && is_array($request->get('optional'))) {
            $optional = array_filter(array_map(function ($value) {
                return filter_var($value, FILTER_VALIDATE_INT);
            }, $request->get('optional')));
        }

        $fields = [
            'committer'       => $previous->committer,
            'committer_email' => $previous->committer_email,
            'commit'          => $previous->commit,
            'project_id'      => $previous->project_id,
            'branch'          => $previous->branch,
            'reason'          => trans('deployments.rollback_reason', [
                                    'reason'          => $request->get('reason'),
                                    'id'              => $previous->id,
                                    'commit'          => $previous->short_commit]),
            'optional'        => $optional,
            'environments'    => $previous->environments->pluck('id')->toArray(),
        ];

        $fields['user_id'] = Auth::user()->id;

        dispatch(new CreateDeploymentJob($previous->project, $fields));

        return [
            'success' => true,
        ];
    }

    /**
     * Execute the deployment from draft.
     *
     * @param Deployment $deployment
     *
     * @return Response
     */
    public function deployDraft(Deployment $deployment)
    {
        $this->authorize('deploy', $deployment->project);

        if ($deployment->isDraft()) {
            dispatch(new DeployDraftJob($deployment));
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Abort a deployment.
     *
     * @param Deployment $deployment
     *
     * @return Response
     */
    public function abort(Deployment $deployment)
    {
        $this->authorize('deploy', $deployment->project);

        if (!$deployment->isAborting()) {
            $deployment->status = Deployment::ABORTING;
            $deployment->save();

            dispatch(new AbortDeploymentJob($deployment));
        }

        return redirect()->route('deployments', [
            'id' => $deployment->id,
        ]);
    }
}
