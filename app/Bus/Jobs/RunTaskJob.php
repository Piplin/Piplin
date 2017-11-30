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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Queue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Piplin\Bus\Events\TaskFinishedEvent;
use Piplin\Bus\Jobs\Task\RunDeployTaskStepsJob;
use Piplin\Bus\Jobs\Task\RunBuildTaskStepsJob;
use Piplin\Bus\Jobs\Release\CreateArtifactArchiveJob;
use Piplin\Bus\Jobs\Repository\CreateArchiveJob;
use Piplin\Bus\Jobs\Repository\GetCommitDetailsJob;
use Piplin\Bus\Jobs\Repository\UpdateGitMirrorJob;
use Piplin\Models\BuildPlan;
use Piplin\Models\Command as Stage;
use Piplin\Models\Task;
use Piplin\Models\TaskStep;
use Piplin\Models\Environment;
use Piplin\Models\Project;
use Piplin\Models\Release;
use Piplin\Models\Server;
use Piplin\Models\ServerLog;
use Piplin\Models\User;
use Piplin\Services\Scripts\Runner as Process;

/**
 * Run an actual task.
 */
class RunTaskJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * @var int
     */
    public $timeout = 0;

    /**
     * @var Task
     */
    private $task;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var string
     */
    private $private_key;

    /**
     * @var string
     */
    private $release_archive;

    /**
     * Create a new command instance.
     *
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task    = $task;
        $this->project = $task->targetable->project;
    }

    /**
     * Overwrite the queue method to push to a different queue.
     *
     * @param Queue      $queue
     * @param RunTaskJob $command
     */
    public function queue(Queue $queue, $command)
    {
        $queue->pushOn('piplin-high', $command);
    }

    /**
     * Execute the command.
     */
    public function handle()
    {
        $this->task->started_at = Carbon::now();
        $this->task->status     = Task::RUNNING;
        $this->task->save();

        $this->project->status = Project::RUNNING;
        $this->project->save();

        $this->private_key = tempnam(storage_path('app/'), 'sshkey');
        file_put_contents($this->private_key, $this->project->private_key_content);
        chmod($this->private_key, 0600);

        $this->release_archive = $this->project->id . '_' . $this->task->release_id . '.tar.gz';

        if ($this->task->commit === Task::LOADING) {
            $commit = $this->task->branch;
        } else {
            $commit = $this->task->commit;
        }

        try {
            if ($this->task->payload && $this->task->payload->source == 'release') {
                $releaseId = $this->task->payload->source_release;
                $release = Release::findOrFail($releaseId);
                $this->dispatch(new CreateArtifactArchiveJob($this->task, $release, $this->release_archive));
            } else {
                $this->dispatch(new UpdateGitMirrorJob($this->project));

                $this->dispatch(new GetCommitDetailsJob($this->project, $commit, function ($gitInfo) {
                    $this->updateRepoInfo($gitInfo);
                }));

                $this->dispatch(new CreateArchiveJob($this->project, $this->task->commit, $this->release_archive));
            }

            $runTaskStepsClass = RunDeployTaskStepsJob::class;
            if ($this->task->targetable instanceof BuildPlan) {
                $runTaskStepsClass = RunBuildTaskStepsJob::class;
            }
            $this->dispatch(new $runTaskStepsClass($this->task, $this->private_key, $this->release_archive));

            $this->task->status = Task::COMPLETED;
            $this->project->status    = Project::FINISHED;
        } catch (\Exception $error) {
            $this->task->status = Task::FAILED;
            $this->project->status    = Project::FAILED;

            if ($error->getMessage() === 'Cancelled') {
                $this->task->status = Task::ABORTED;
            }

            $this->task->output = $error->getMessage();

            $this->cancelPendingSteps($this->task->steps);

            if (isset($step)) {
                // Cleanup the release if it has not been activated
                if ($step->stage <= Stage::DO_ACTIVATE) {
                    $this->cleanupTask();
                } else {
                    $this->task->status = Task::COMPLETED_WITH_ERRORS;
                    $this->project->status    = Project::FINISHED;
                }
            }
        }

        if ($this->task->status !== Task::ABORTED) {
            $this->task->finished_at =  Carbon::now();
        }

        $this->task->save();

        $this->project->last_run = $this->task->finished_at;
        $this->project->save();

        $this->updateEnvironmentsInfo();

        // Notify user or others the deployment has been finished
        event(new TaskFinishedEvent($this->task));

        unlink($this->private_key);

        if (file_exists(storage_path('app/' . $this->release_archive))) {
            unlink(storage_path('app/' . $this->release_archive));
        }
    }

    /**
     * Gets the latest log entry and updates.
     *
     * @param string $gitInfo
     */
    private function updateRepoInfo($gitInfo)
    {
        list($commit, $committer, $email) = explode("\x09", $gitInfo);

        $this->task->commit          = $commit;
        $this->task->committer       = trim($committer);
        $this->task->committer_email = trim($email);

        if (!$this->task->user_id && !$this->task->source) {
            $user = User::where('email', $this->task->committer_email)->first();

            if ($user) {
                $this->task->user_id = $user->id;
            }
        }
    }

    /**
     * Remove left over artifacts from a failed deploy on each server.
     */
    private function cleanupTask()
    {
        $servers = $this->task->environments->pluck('servers')->flatten();

        foreach ($servers as $server) {
            if (!$server->enabled) {
                continue;
            }

            $process = new Process('deploy.CleanupFailedRelease', [
                'project_path'   => $this->project->clean_deploy_path,
                'release_path'   => $this->project->clean_deploy_path . '/releases/' . $this->task->release_id,
                'remote_archive' => $this->project->clean_deploy_path . '/' . $this->release_archive,
            ]);

            $process->setServer($server, $this->private_key)
                    ->run();
        }
    }

    /**
     * Finds all pending steps and marks them as cancelled.
     */
    private function cancelPendingSteps()
    {
        foreach ($this->task->steps as $step) {
            foreach ($step->logs as $log) {
                if ($log->status === ServerLog::PENDING) {
                    $log->status = ServerLog::CANCELLED;
                    $log->save();
                }
            }
        }
    }

    /**
     * Update the status and last run time of the deployment enviroments.
     */
    private function updateEnvironmentsInfo()
    {
        foreach ($this->task->environments as $environment) {
            $environment->last_run = $this->project->last_run;
            $environment->status   = $this->project->status;
            $environment->save();
        }
    }
}
