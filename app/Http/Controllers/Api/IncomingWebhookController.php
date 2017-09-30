<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Api;

use Fixhub\Http\Controllers\Controller;
use Fixhub\Bus\Jobs\AbortDeploymentJob;
use Fixhub\Services\Webhooks\Beanstalkapp;
use Fixhub\Services\Webhooks\Bitbucket;
use Fixhub\Services\Webhooks\Custom;
use Fixhub\Services\Webhooks\Github;
use Fixhub\Services\Webhooks\Gitlab;
use Fixhub\Models\Deployment;
use Fixhub\Models\Project;
use Illuminate\Http\Request;

/**
 * The deployment incoming-webhook controller.
 */
class IncomingWebhookController extends Controller
{
    /**
     * List of supported service classes.
     * @var array
     */
    private $services = [
        Beanstalkapp::class,
        Bitbucket::class,
        Github::class,
        Gitlab::class,
    ];

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->services[] = Custom::class;
    }

    /**
     * Handles incoming requests to trigger deploy.
     *
     * @param  Request  $request
     * @param  string   $hash
     * @return Response
     */
    public function webhook(Request $request, $hash)
    {
        $project = Project::where('hash', $hash)->firstOrFail();

        $success = false;
        if ($project->servers->where('deploy_code', true)->count() > 0) {
            $payload = $this->parseWebhookRequest($request, $project);

            // Todo: Need improvement.
            //if (is_array($payload) && ($project->allow_other_branch || $project->branch == $payload['branch'])) {
            if (is_array($payload)) {
                $this->abortQueued($project->id);
                Deployment::create($payload);

                $success = true;
            }
        }

        return [
            'success' => $success,
        ];
    }

    /**
     * Goes through the various webhook integrations as checks if the request is for them and parses it.
     * Then adds the various additional details required to trigger a deployment.
     *
     * @param  Request $request
     * @param  Project $project
     * @return mixed   Either an array of parameters for the deployment config, or false if it is invalid.
     */
    private function parseWebhookRequest(Request $request, Project $project)
    {
        foreach ($this->services as $service) {
            $integration = new $service($request);

            if ($integration->isRequestOrigin()) {
                return $this->appendProjectSettings($integration->handlePush(), $request, $project);
            }
        }

        return false;
    }

    /**
     * Takes the data returned from the webhook request and then adds projects own data, such as project ID
     * and runs any checks such as checks the branch is allowed to be deployed.
     *
     * @param  mixed   $payload
     * @param  Request $request
     * @param  Project $project
     * @return mixed   Either an array of the complete deployment config, or false if it is invalid.
     */
    private function appendProjectSettings($payload, Request $request, Project $project)
    {
        // If the payload is empty return false
        if (!is_array($payload) || !count($payload)) {
            return false;
        }

        $payload['project_id'] = $project->id;

        // If there is no branch set get it from the project
        if (is_null($payload['branch']) || empty($payload['branch'])) {
            $payload['branch'] = $project->branch;
        }

        // If the project doesn't allow other branches check the requested branch is the correct one
        if (!$project->allow_other_branch && $payload['branch'] !== $project->branch) {
            return false;
        }

        $payload['optional'] = [];

        // Check if the commands input is set, if so explode on comma and filter out any invalid commands
        if ($request->has('commands')) {
            $valid     = $project->commands->pluck('id');
            $requested = explode(',', $request->get('commands'));

            $payload['optional'] = collect($requested)->unique()
                                                      ->intersect($valid)
                                                      ->toArray();
        }

        $payload['environments'] = [];
        if ($request->has('environments')) {
            $valid     = $project->environments->pluck('id');
            $requested = explode(',', $request->get('environments'));

            $payload['environments'] = collect($requested)->unique()
                                                      ->intersect($valid)
                                                      ->toArray();
        }

        // Check if the request has an update_only query string and if so check the branch matches
        if ($request->has('update_only') && $request->get('update_only') !== false) {
            $deployment = Deployment::where('project_id', $project->id)
                           ->where('status', Deployment::COMPLETED)
                           ->whereNotNull('started_at')
                           ->orderBy('started_at', 'DESC')
                           ->first();

            if (!$deployment || $deployment->branch !== $payload['branch']) {
                return false;
            }
        }

        return $payload;
    }

    /**
     * Gets all pending and running deployments for a project and aborts them.
     *
     * @param  int  $project_id
     * @return void
     */
    private function abortQueued($project_id)
    {
        $deployments = Deployment::where('project_id', $project_id)
                                   ->whereIn('status', [Deployment::DEPLOYING, Deployment::PENDING])
                                   ->orderBy('started_at', 'DESC')
                                   ->get();

        foreach ($deployments as $deployment) {
            $deployment->status = Deployment::ABORTING;
            $deployment->save();

            dispatch(new AbortDeploymentJob($deployment));

            if ($deployment->is_webhook) {
                $deployment->delete();
            }
        }
    }
}
