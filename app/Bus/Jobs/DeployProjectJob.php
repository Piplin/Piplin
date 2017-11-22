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
use Piplin\Bus\Jobs\Task\RunStepsJob;
use Piplin\Bus\Jobs\Repository\CreateArchiveJob;
use Piplin\Bus\Jobs\Repository\GetCommitDetailsJob;
use Piplin\Bus\Jobs\Repository\UpdateGitMirrorJob;
use Piplin\Models\Command as Stage;
use Piplin\Models\Task;
use Piplin\Models\TaskStep;
use Piplin\Models\Environment;
use Piplin\Models\Project;
use Piplin\Models\Server;
use Piplin\Models\ServerLog;
use Piplin\Models\User;
use Piplin\Services\Scripts\Runner as Process;

/**
 * Deploys an actual project.
 */
class DeployProjectJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * @var int
     */
    public $timeout = 0;

    /**
     * @var Task
     */
    private $deployment;

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
     * @param Task $deployment
     */
    public function __construct(Task $deployment)
    {
        $this->deployment = $deployment;
        $this->project    = $deployment->project;
    }

    /**
     * Overwrite the queue method to push to a different queue.
     *
     * @param Queue            $queue
     * @param DeployProjectJob $command
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
        $this->deployment->started_at = Carbon::now();
        $this->deployment->status     = Task::RUNNING;
        $this->deployment->save();

        $this->project->status = Project::RUNNING;
        $this->project->save();

        $this->private_key = tempnam(storage_path('app/'), 'sshkey');
        file_put_contents($this->private_key, $this->project->private_key_content);
        chmod($this->private_key, 0600);

        $this->release_archive = $this->project->id . '_' . $this->deployment->release_id . '.tar.gz';

        if ($this->deployment->commit === Task::LOADING) {
            $commit = $this->deployment->branch;
        } else {
            $commit = $this->deployment->commit;
        }

        try {
            $this->dispatch(new UpdateGitMirrorJob($this->project));

            $this->dispatch(new GetCommitDetailsJob($this->project, $commit, function ($gitInfo) {
                $this->updateRepoInfo($gitInfo);
            }));

            $this->dispatch(new CreateArchiveJob($this->project, $this->deployment->commit, $this->release_archive));

            $this->dispatch(new RunStepsJob($this->deployment, $this->private_key, $this->release_archive));

            $this->deployment->status = Task::COMPLETED;
            $this->project->status    = Project::FINISHED;
        } catch (\Exception $error) {
            $this->deployment->status = Task::FAILED;
            $this->project->status    = Project::FAILED;

            if ($error->getMessage() === 'Cancelled') {
                $this->deployment->status = Task::ABORTED;
            }

            $this->deployment->output = $error->getMessage();

            $this->cancelPendingSteps($this->deployment->steps);

            if (isset($step)) {
                // Cleanup the release if it has not been activated
                if ($step->stage <= Stage::DO_ACTIVATE) {
                    $this->cleanupDeployment();
                } else {
                    $this->deployment->status = Task::COMPLETED_WITH_ERRORS;
                    $this->project->status    = Project::FINISHED;
                }
            }
        }

        if ($this->deployment->status !== Task::ABORTED) {
            $this->deployment->finished_at =  Carbon::now();
        }

        $this->deployment->save();

        $this->project->last_run = $this->deployment->finished_at;
        $this->project->save();

        $this->updateEnvironmentsInfo();

        // Notify user or others the deployment has been finished
        event(new TaskFinishedEvent($this->deployment));

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

        $this->deployment->commit          = $commit;
        $this->deployment->committer       = trim($committer);
        $this->deployment->committer_email = trim($email);

        if (!$this->deployment->user_id && !$this->deployment->source) {
            $user = User::where('email', $this->deployment->committer_email)->first();

            if ($user) {
                $this->deployment->user_id = $user->id;
            }
        }
    }

    /**
     * Remove left over artifacts from a failed deploy on each server.
     */
    private function cleanupDeployment()
    {
        $servers = $this->deployment->environments->pluck('servers')->flatten();

        foreach ($servers as $server) {
            if (!$server->enabled) {
                continue;
            }

            $process = new Process('deploy.CleanupFailedRelease', [
                'project_path'   => $this->project->clean_deploy_path,
                'release_path'   => $this->project->clean_deploy_path . '/releases/' . $this->deployment->release_id,
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
        foreach ($this->deployment->steps as $step) {
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
        foreach ($this->deployment->environments as $environment) {
            $environment->last_run = $this->project->last_run;
            $environment->status   = $this->project->status;
            $environment->save();
        }
    }
}
