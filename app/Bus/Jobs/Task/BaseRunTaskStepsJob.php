<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Jobs\Task;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Queue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Piplin\Bus\Jobs\AbortTaskJob;
use Piplin\Bus\Jobs\Job;
use Piplin\Bus\Jobs\UpdateGitReferencesJob;
use Piplin\Models\Command as Stage;
use Piplin\Models\Task;
use Piplin\Models\TaskStep;
use Piplin\Models\Environment;
use Piplin\Models\BuildPlan;
use Piplin\Models\Project;
use Piplin\Models\Release;
use Piplin\Models\Server;
use Piplin\Models\ServerLog;
use Piplin\Models\User;
use Piplin\Services\Scripts\Parser as ScriptParser;
use Piplin\Services\Scripts\Runner as Process;
use Illuminate\Bus\Queueable;

/**
 * Abstract class of run task steps.
 */
abstract class BaseRunTaskStepsJob
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "onQueue" and "delay" queue helper methods.
    |
    */

    use Queueable, SerializesModels, DispatchesJobs;

    /**
     * @var int
     */
    public $timeout = 0;

    /**
     * @var Task
     */
    protected $task;

    /**
     * @var mixed
     */
    protected $plan;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var string
     */
    protected $cache_key;

    /**
     * @var string
     */
    protected $private_key;

    /**
     * @var string
     */
    protected $release_archive;

    /**
     *
     * @var bool
     */
    protected $isBuild;

    /**
     * Create a new job instance.
     *
     * @param Task   $task
     * @param string $private_key
     * @param string $release_archive
     *
     * @return void
     */
    public function __construct(Task $task, $private_key, $release_archive)
    {
        $this->task            = $task;
        $this->plan            = $task->targetable;
        $this->project         = $task->project;
        $this->private_key     = $private_key;
        $this->cache_key       = AbortTaskJob::CACHE_KEY_PREFIX . $task->id;
        $this->release_archive = $release_archive;
        if ($this->plan instanceof BuildPlan) {
            $this->isBuild = true;
        } else {
            $this->isBuild = false;
        }
    }

    /**
     * Overwrite the queue method to push to a different queue.
     *
     * @param Queue $queue
     * @param Job   $command
     */
    public function queue(Queue $queue, $command)
    {
        $queue->pushOn('piplin-high', $command);
    }

    /**
     * Sends a file to a remote server.
     *
     * @param  string           $local_file
     * @param  string           $remote_file
     * @param  ServerLog        $log
     * @throws RuntimeException
     */
    protected function sendFile($local_file, $remote_file, ServerLog $log)
    {
        $process = new Process('deploy.SendFileToServer', [
            'port'        => $log->server->port,
            'private_key' => $this->private_key,
            'local_file'  => $local_file,
            'remote_file' => $remote_file,
            'username'    => $log->server->user,
            'ip_address'  => $log->server->ip_address,
        ]);

        $output = '';
        $process->run(function ($type, $output_line) use (&$output, &$log) {
            if ($type === \Symfony\Component\Process\Process::ERR) {
                $output .= $this->logError($output_line);
            } else {
                $output .= $this->logSuccess($output_line);
            }

            $log->output = $output;
            $log->save();
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }

    /**
     * Generates the list of tokens for the scripts.
     *
     * @param TaskStep $step
     * @param Server     $server
     */
    protected function getTokenList(TaskStep $step)
    {
        $project_path = $this->project->clean_deploy_path;

        $releases_dir       = $project_path . '/releases';
        $builds_dir         = $project_path . '/builds';
        $release_shared_dir = $project_path . '/shared';
        $remote_archive     = $project_path . '/' . $this->release_archive;

        $tokens = [
            'project_path'    => $project_path,
            'branch'          => $this->task->branch,
            'sha'             => $this->task->commit,
            'short_sha'       => $this->task->short_commit,
            'committer_email' => $this->task->committer_email,
            'committer_name'  => $this->task->committer,
        ];

        if ($this->isBuild === true) {
            $tokens = array_merge($tokens, [
                'build'         => $this->task->release_id,
                'build_path'    => $builds_dir . '/' . $this->task->release_id,
            ]);
        } else {
            $author_email = '';
            $author_name  = 'webhook';
            if ($this->task->user) {
                $author_name  = $this->task->user->name;
                $author_email = $this->task->user->email;
            } elseif ($this->task->is_webhook && !empty($this->task->source)) {
                $author_name = $this->task->source;
            }

            $tokens = array_merge($tokens, [
                'release'         => $this->task->release_id,
                'release_path'    => $releases_dir . '/' . $this->task->release_id,
                'author_email'  => $author_email,
                'author_name'   => $author_name,
            ]);

            if ($this->task->payload && $this->task->payload->source == 'release') {
                $release = Release::findOrFail($this->task->payload->source_release);
                $tokens = array_merge($tokens, [
                    'build_release' => $release->name,
                ]);
            }
        }

        if (!$step->isCustom()) {
            $tokens = array_merge($tokens, [
                'remote_archive' => $remote_archive,
                'builds_to_keep' => $this->project->builds_to_keep + 1,
            ]);

            if ($this->isBuild === true) {
                $tokens = array_merge($tokens, [
                    'builds_path'  => $builds_dir,
                ]);
            } else {
                $tokens = array_merge($tokens, [
                    'shared_path'   => $release_shared_dir,
                    'releases_path' => $releases_dir,
                ]);
            }
        }

        return $tokens;
    }

    /**
     * Generates an error string to log to the DB.
     *
     * @param string $message
     */
    protected function logError($message)
    {
        return '<error>' . $message . '</error>';
    }
    /**
     * Generates an general output string to log to the DB.
     *
     * @param string $message
     */
    protected function logSuccess($message)
    {
        return '<info>' . $message . '</info>';
    }
}
