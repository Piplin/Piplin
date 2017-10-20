<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Jobs;

use Carbon\Carbon;
use Fixhub\Bus\Jobs\DeployProjectJob;
use Fixhub\Models\Command as Stage;
use Fixhub\Models\Deployment;
use Fixhub\Models\DeployStep;
use Fixhub\Models\Project;
use Fixhub\Models\ServerLog;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;

/**
 * Generates the required database entries to queue a deployment.
 */
class SetupDeploymentJob extends Job
{
    use DispatchesJobs;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var Deployment
     */
    private $deployment;

    /**
     * @var array
     */
    private $optional;

    /**
     * @var array
     */
    private $environmentIds;

    /**
     * Create a new command instance.
     *
     * @param Deployment $deployment
     * @param array $environmentIds
     * @param array $optional
     *
     * @return void
     */
    public function __construct(Deployment $deployment, array $environmentIds = [], array $optional = [])
    {
        $this->deployment     = $deployment;
        $this->environmentIds = $environmentIds;
        $this->optional       = $optional;
        $this->project        = $deployment->project;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->setDeploymentEnvironments();

        $this->setDeploymentStatus();

        $hooks = $this->buildCommandList();

        foreach (array_keys($hooks) as $stage) {
            $before = $stage - 1;
            $after  = $stage + 1;

            if (isset($hooks[$stage]['before'])) {
                foreach ($hooks[$stage]['before'] as $hook) {
                    $this->createCommandStep($before, $hook);
                }
            }

            $this->createDeployStep($stage);

            if (isset($hooks[$stage]['after'])) {
                foreach ($hooks[$stage]['after'] as $hook) {
                    $this->createCommandStep($after, $hook);
                }
            }
        }

        $this->dispatch(new DeployProjectJob($this->deployment));
    }

    /**
     * Set the deployment environment ids.
     *
     * @return void
     */
    private function setDeploymentEnvironments()
    {
        if (!$this->environmentIds) {
            $this->environmentIds = $this->project->environments
                    ->where('default_on', true)
                    ->pluck('id')->toArray();
        }

        $this->deployment->environments()->sync($this->environmentIds);
        $this->deployment->environments; // Triggers the loading
    }

    /**
     * Builds up a list of commands to run before/after each stage.
     *
     * @return array
     */
    private function buildCommandList()
    {
        $hooks = [
            Stage::DO_CLONE    => null,
            Stage::DO_INSTALL  => null,
            Stage::DO_ACTIVATE => null,
            Stage::DO_PURGE    => null,
        ];

        foreach ($this->project->commands()->orderBy('order', 'asc')->get() as $command) {
            $action = $command->step - 1;
            $when   = ($command->step % 3 === 0 ? 'after' : 'before');
            if ($when === 'before') {
                $action = $command->step + 1;
            }

            // Check if the command is optional, and if it is check it exists in the optional array
            if ($command->optional && !in_array($command->id, $this->optional, true)) {
                continue;
            }

            if (!isset($hooks[$action]) || !is_array($hooks[$action])) {
                $hooks[$action] = [];
            }

            if (!isset($hooks[$action][$when])) {
                $hooks[$action][$when] = [];
            }

            $hooks[$action][$when][] = $command;
        }

        return $hooks;
    }

    /**
     * Sets the deployment to pending.
     *
     * @return void
     */
    private function setDeploymentStatus()
    {

        $this->deployment->status = Deployment::PENDING;

        $this->deployment->started_at = Carbon::now();
        $this->deployment->project_id = $this->project->id;

        if (Auth::check()) {
            $this->deployment->user_id = Auth::user()->id;
        } else {
            $this->deployment->is_webhook = true;
        }

        $this->deployment->committer = $this->deployment->committer ?: Deployment::LOADING;
        $this->deployment->commit    = $this->deployment->commit ?: Deployment::LOADING;
        $this->deployment->save();

        $this->deployment->project->status = Project::PENDING;
        $this->deployment->project->save();
    }

    /**
     * Create an instance of DeployStep and a ServerLog entry for each server assigned to the command.
     *
     * @param  int     $stage
     * @param  Stage $command
     *
     * @return void
     */
    private function createCommandStep($stage, Stage $command)
    {
        $step = DeployStep::create([
            'stage'         => $stage,
            'deployment_id' => $this->deployment->id,
            'command_id'    => $command->id,
        ]);

        foreach ($command->environments as $environment) {
            if ($this->deployment->environments()->find($environment->id) === null) {
                continue;
            }
            foreach ($environment->servers->where('enabled', true) as $server) {
                ServerLog::create([
                    'server_id'      => $server->id,
                    'deploy_step_id' => $step->id,
                ]);
            }
        }
    }

    /**
     * Create an instance of DeployStep and a ServerLog entry for each server which can have code deployed.
     *
     * @param  int  $stage
     *
     * @return void
     */
    private function createDeployStep($stage)
    {
        $step = DeployStep::create([
            'stage'         => $stage,
            'deployment_id' => $this->deployment->id,
        ]);

        $servers = $this->deployment->environments->pluck('servers')->flatten();

        foreach ($servers as $server) {
            if (!$server->enabled) {
                continue;
            }

            ServerLog::create([
                    'server_id'      => $server->id,
                    'deploy_step_id' => $step->id,
                ]);
        }
    }
}
