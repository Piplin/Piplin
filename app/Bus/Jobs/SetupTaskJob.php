<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Jobs;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Piplin\Models\Command as Stage;
use Piplin\Models\Task;
use Piplin\Models\TaskStep;
use Piplin\Models\Environment;
use Piplin\Models\BuildPlan;
use Piplin\Models\Project;
use Piplin\Models\ServerLog;

/**
 * Generates the required database entries to queue a task.
 */
class SetupTaskJob extends Job
{
    use DispatchesJobs;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var mixed
     */
    private $plan;

    /**
     * @var Task
     */
    private $task;

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
     * @param Task  $task
     * @param array $environmentIds
     * @param array $optional
     *
     * @return void
     */
    public function __construct(Task $task, array $environmentIds = [], array $optional = [])
    {
        $this->task           = $task;
        $this->environmentIds = $environmentIds;
        $this->optional       = $optional;
        $this->plan           = $task->targetable;
        $this->project        = $this->plan->project;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        // Build task
        if ($this->plan && $this->plan instanceof BuildPlan) {
            $stakes = [
                Stage::DO_PREPARE => null,
                Stage::DO_BUILD   => null,
                Stage::DO_TEST    => null,
                Stage::DO_RESULT  => null,
            ];
            $commandStep = 'createBuildCommandStep';
            $runStep     = 'createBuildStep';
            // Deploy task
        } else {
            $this->setTaskEnvironments();
            $stakes = [
                Stage::DO_CLONE    => null,
                Stage::DO_INSTALL  => null,
                Stage::DO_ACTIVATE => null,
                Stage::DO_PURGE    => null,
            ];
            $commandStep = 'createCommandStep';
            $runStep     = 'createTaskStep';
        }

        $hooks = $this->buildCommandList($this->plan, $stakes);

        $this->setTaskStatus();

        foreach (array_keys($hooks) as $stage) {
            $before = $stage - 1;
            $after  = $stage + 1;

            if (isset($hooks[$stage]['before'])) {
                foreach ($hooks[$stage]['before'] as $hook) {
                    $this->{$commandStep}($before, $hook);
                }
            }

            $this->{$runStep}($stage);

            if (isset($hooks[$stage]['after'])) {
                foreach ($hooks[$stage]['after'] as $hook) {
                    $this->{$commandStep}($after, $hook);
                }
            }
        }

        if (!$this->task->isDraft()) {
            $this->dispatch(new RunTaskJob($this->task));
        }
    }

    /**
     * Set the deployment environment ids.
     *
     * @return void
     */
    private function setTaskEnvironments()
    {
        if (!$this->environmentIds) {
            $this->environmentIds = $this->plan->environments
                    ->where('default_on', true)
                    ->pluck('id')->toArray();
        }

        $this->task->environments()->sync($this->environmentIds);
        $this->task->environments; // Triggers the loading
    }

    /**
     * Builds up a list of commands to run before/after each stage.
     *
     * @param mixed $targetable
     * @param array $stakes
     *
     * @return array
     */
    private function buildCommandList($targetable, $stakes)
    {
        $hooks = $stakes;

        foreach ($targetable->commands()->orderBy('order', 'asc')->get() as $command) {
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
    private function setTaskStatus()
    {
        if ($this->task->status !== Task::DRAFT) {
            $this->task->status          = Task::PENDING;
            $this->task->project->status = Project::PENDING;
        }

        $this->task->started_at = Carbon::now();
        $this->task->project_id = $this->project->id;

        if (!$this->task->user_id) {
            $this->task->is_webhook = true;
        }

        $this->task->committer = $this->task->committer ?: Task::LOADING;
        $this->task->commit    = $this->task->commit ?: Task::LOADING;
        $this->task->save();

        $this->task->project->save();
    }

    /**
     * Create an instance of TaskStep and a ServerLog entry for each server assigned to the command.
     *
     * @param int   $stage
     * @param Stage $command
     *
     * @return void
     */
    private function createBuildCommandStep($stage, Stage $command)
    {
        $step = TaskStep::create([
            'stage'      => $stage,
            'task_id'    => $this->task->id,
            'command_id' => $command->id,
        ]);

        $this->createBuildServerlog($this->task->targetable, $step);
    }

    /**
     * Create an instance of TaskStep and a ServerLog entry for each server which can have code deployed.
     *
     * @param int $stage
     *
     * @return void
     */
    private function createBuildStep($stage)
    {
        $step = TaskStep::create([
            'stage'   => $stage,
            'task_id' => $this->task->id,
        ]);

        $this->createBuildServerlog($this->plan, $step);
    }

    /**
     * Create server logs.
     *
     * @param BuildPlan $buildPlan
     * @param inet      $step
     *
     * @return void
     */
    private function createBuildServerlog(BuildPlan $buildPlan, $step)
    {
        foreach ($buildPlan->servers->where('enabled', true) as $server) {
            ServerLog::create([
                'server_id'      => $server->id,
                'task_step_id' => $step->id,
            ]);
        }
    }

    /**
     * Create an instance of TaskStep and a ServerLog entry for each server assigned to the command.
     *
     * @param int   $stage
     * @param Stage $command
     *
     * @return void
     */
    private function createCommandStep($stage, Stage $command)
    {
        $step = TaskStep::create([
            'stage'         => $stage,
            'task_id' => $this->task->id,
            'command_id'    => $command->id,
        ]);

        foreach ($command->environments as $environment) {
            if ($this->task->environments()->find($environment->id) === null) {
                continue;
            }

            $this->createServerLog($environment, $step);
        }
    }

    /**
     * Create an instance of TaskStep and a ServerLog entry for each server which can have code deployed.
     *
     * @param int $stage
     *
     * @return void
     */
    private function createTaskStep($stage)
    {
        $step = TaskStep::create([
            'stage'   => $stage,
            'task_id' => $this->task->id,
        ]);

        foreach ($this->task->environments as $environment) {
            $this->createServerLog($environment, $step);
        }
    }

    /**
     * Create server logs.
     *
     * @param Environment $environment
     * @param inet        $step
     *
     * @return void
     */
    private function createServerLog(Environment $environment, $step)
    {
        foreach ($environment->cabinets->pluck('servers')->flatten() as $server) {
            if (!$server->enabled) {
                continue;
            }

            ServerLog::create([
                'environment_id' => $environment->id,
                'server_id'      => $server->id,
                'task_step_id' => $step->id,
            ]);
        }

        foreach ($environment->servers->where('enabled', true) as $server) {
            ServerLog::create([
                'environment_id' => $environment->id,
                'server_id'      => $server->id,
                'task_step_id' => $step->id,
            ]);
        }
    }
}
