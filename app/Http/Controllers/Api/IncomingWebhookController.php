<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Api;

use Illuminate\Http\Request;
use Piplin\Bus\Jobs\AbortTaskJob;
use Piplin\Bus\Jobs\CreateTaskJob;
use Piplin\Http\Controllers\Controller;
use Piplin\Models\Task;
use Piplin\Models\Project;
use Piplin\Services\Webhooks\Beanstalkapp;
use Piplin\Services\Webhooks\Bitbucket;
use Piplin\Services\Webhooks\Custom;
use Piplin\Services\Webhooks\Github;
use Piplin\Services\Webhooks\Gitlab;
use Piplin\Services\Webhooks\Gogs;
use Piplin\Services\Webhooks\Oschina;

/**
 * The task incoming-webhook controller.
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
        Gogs::class,
        Oschina::class,
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
     * @param Request $request
     * @param string  $hash
     *
     * @return Response
     */
    public function deploy(Request $request, $hash)
    {
        $project = Project::where('hash', $hash)->firstOrFail();

        $deployPlan = $project->deployPlan;

        $success = false;
        if ($deployPlan && $deployPlan->environments->count() > 0) {
            $payload = $this->parseWebhookRequest($request, $project);

            if (is_array($payload) && ($project->allow_other_branch || $project->branch === $payload['branch'])) {
                $this->abortQueued($project->id);
                $payload['targetable_type'] = get_class($deployPlan);
                $payload['targetable_id'] = $deployPlan->id;
                dispatch(new CreateTaskJob($project, $payload));

                $success = true;
            }
        }

        return [
            'success' => $success,
        ];
    }

    /**
     * Handles incoming requests to trigger build.
     *
     * @param Request $request
     * @param string  $hash
     *
     * @return Response
     */
    public function build(Request $request, $hash)
    {
        $project = Project::where('hash', $hash)->firstOrFail();

        $buildPlan = $project->buildPlan;

        $success = false;
        if ($buildPlan && $buildPlan->servers->count() > 0) {
            $payload = $this->parseWebhookRequest($request, $project);

            if (is_array($payload) && ($project->allow_other_branch || $project->branch === $payload['branch'])) {
                $this->abortQueued($project->id);
                $payload['targetable_type'] = get_class($buildPlan);
                $payload['targetable_id'] = $buildPlan->id;
                dispatch(new CreateTaskJob($project, $payload));

                $success = true;
            }
        }

        return [
            'success' => $success,
        ];
    }

    /**
     * Goes through the various webhook integrations as checks if the request is for them and parses it.
     * Then adds the various additional details required to trigger a task.
     *
     * @param Request $request
     * @param Project $project
     *
     * @return mixed Either an array of parameters for the task config, or false if it is invalid.
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
     * @param mixed   $payload
     * @param Request $request
     * @param Project $project
     *
     * @return mixed Either an array of the complete task config, or false if it is invalid.
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
            $valid     = $project->deployPlan->environments->pluck('id');
            $requested = explode(',', $request->get('environments'));

            $payload['environments'] = collect($requested)->unique()
                                                      ->intersect($valid)
                                                      ->toArray();
        }

        // Check if the request has an update_only query string and if so check the branch matches
        if ($request->has('update_only') && $request->get('update_only') !== false) {
            $task = Task::where('project_id', $project->id)
                           ->where('status', Task::COMPLETED)
                           ->whereNotNull('started_at')
                           ->orderBy('started_at', 'DESC')
                           ->first();

            if (!$task || $task->branch !== $payload['branch']) {
                return false;
            }
        }

        return $payload;
    }

    /**
     * Gets all pending and running tasks for a project and aborts them.
     *
     * @param int $project_id
     *
     * @return void
     */
    private function abortQueued($project_id)
    {
        $tasks = Task::where('project_id', $project_id)
                                   ->whereIn('status', [Task::RUNNING, Task::PENDING])
                                   ->orderBy('started_at', 'DESC')
                                   ->get();

        foreach ($tasks as $task) {
            $task->status = Task::ABORTING;
            $task->save();

            dispatch(new AbortTaskJob($task));

            if ($task->is_webhook) {
                $task->delete();
            }
        }
    }
}
