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
use Piplin\Models\Server;
use Piplin\Models\ServerLog;
use Piplin\Models\User;
use Piplin\Services\Scripts\Parser as ScriptParser;
use Piplin\Services\Scripts\Runner as Process;

/**
 * Run steps of the deployment.
 */
class RunStepsJob extends Job
{
    use SerializesModels, DispatchesJobs;

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
    private $cache_key;

    /**
     * @var string
     */
    private $private_key;

    /**
     * @var string
     */
    private $release_archive;

    /**
     *
     * @var bool
     */
    private $isBuild;

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
        $this->project         = $task->project;
        $this->private_key     = $private_key;
        $this->cache_key       = AbortTaskJob::CACHE_KEY_PREFIX . $task->id;
        $this->release_archive = $release_archive;
        if ($this->task->targetable && $this->task->targetable instanceof BuildPlan) {
            $this->isBuild = true;
        } else {
            $this->isBuild = false;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->task->steps as $step) {
            $this->runStep($step);
        }
    }

    /**
     * Executes the commands for a step.
     *
     * @param  TaskStep        $step
     * @throws \RuntimeException
     */
    private function runStep(TaskStep $step)
    {
        foreach ($step->logs as $log) {
            $log->status     = ServerLog::RUNNING;
            $log->started_at =  Carbon::now();
            $log->save();

            try {
                $server = $log->server;

                $this->sendFilesForStep($step, $log);

                $process = $this->buildScript($step, $server, $log);

                $failed    = false;
                $cancelled = false;

                if (!empty($process)) {
                    $output = '';
                    $process->run(function ($type, $output_line) use (&$output, &$log, $process, $step) {
                        if ($type === \Symfony\Component\Process\Process::ERR) {
                            $output .= $this->logError($output_line);
                        } else {
                            $output .= $this->logSuccess($output_line);
                        }

                        $log->output = $output;
                        $log->save();

                        // If there is a cache key, kill the process but leave the key
                        if ($step->stage <= Stage::DO_ACTIVATE && Cache::has($this->cache_key)) {
                            $process->stop(0, SIGINT);

                            $output .= $this->logError('SIGINT');
                        }
                    });

                    if (!$process->isSuccessful()) {
                        $failed = true;
                    }

                    $log->output = $output;
                }

                $this->fetchFilesForStep($step, $log);
            } catch (\Exception $e) {
                $log->output .= $this->logError('[' . $server->ip_address . ']: ' . $e->getMessage());
                $failed = true;
            }

            $log->status = ($failed ? ServerLog::FAILED : ServerLog::COMPLETED);

            // Check if there is a cache key and if so abort
            if (Cache::pull($this->cache_key) !== null) {
                // Only allow aborting if the release has not yet been activated
                if ($step->stage <= Stage::DO_ACTIVATE) {
                    $log->status = ServerLog::CANCELLED;

                    $cancelled = true;
                    $failed    = false;
                }
            }

            $log->finished_at =  Carbon::now();
            $log->save();

            // Throw an exception to prevent any more tasks running
            if ($failed) {
                throw new \RuntimeException('Failed');
            }

            // This is a messy way to do it
            if ($cancelled) {
                throw new \RuntimeException('Cancelled');
            }
        }
    }

    /**
     * Sends the files needed to the server.
     *
     * @param TaskStep $step
     * @param ServerLog  $log
     */
    private function sendFilesForStep(TaskStep $step, ServerLog $log)
    {
        $latest_release_dir = $this->project->clean_deploy_path . '/releases/' . $this->task->release_id;
        $remote_archive     = $this->project->clean_deploy_path . '/' . $this->release_archive;
        $local_archive      = storage_path('app/' . $this->release_archive);
        if (in_array($step->stage, [Stage::DO_CLONE, Stage::DO_PREPARE])) {
            $this->sendFile($local_archive, $remote_archive, $log);
        } elseif ($step->stage === Stage::DO_INSTALL) {
            $this->sendConfigFileFromString($latest_release_dir, $log);
        }
    }

    /**
     * Fetchs the files from the remote agent.
     *
     * @param TaskStep $step
     * @param ServerLog  $log
     */
    private function fetchFilesForStep(TaskStep $step, ServerLog $log)
    {
        // Only custom steps have patterns.
        if ($step->stage < Stage::DO_PREPARE || !$step->isCustom()) {
            return;
        }

        $latest_build_dir = $this->project->clean_deploy_path . '/builds/' . $this->task->release_id;

        foreach ($step->command->patterns as $pattern) {
            if (!$pattern || !$pattern->copy_pattern) {
                continue;
            }

            $this->fetchFile($latest_build_dir.'/'. $pattern->copy_pattern, storage_path('app/artifacts/'), $log);
        }
    }

    /**
     * Sends the config files to the server.
     *
     * @param string    $release_dir
     * @param ServerLog $log
     */
    private function sendConfigFileFromString($release_dir, ServerLog $log)
    {
        foreach ($log->environment->configFiles as $file) {
            $this->sendFileFromString($release_dir . '/' . $file->path, $file->content, $log);
        }
    }

    /**
     * Generates the actual bash commands to run on the server.
     *
     * @param TaskStep $step
     * @param Server     $server
     * @param ServerLog  $log
     */
    private function buildScript(TaskStep $step, Server $server, ServerLog $log)
    {
        $tokens = $this->getTokenList($step, $server);

        // Generate the export
        $prepend = '';
        foreach ($this->project->variables as $variable) {
            $key   = $variable->name;
            $value = $variable->value;

            $prepend .= "export {$key}={$value}" . PHP_EOL;
        }

        // Make release_path as your current path
        if ($step->stage > Stage::DO_INSTALL && $step->stage < Stage::BEFORE_PREPARE) {
            $prepend .= 'cd ' . $tokens['release_path'] . PHP_EOL;
        } elseif ($step->stage > Stage::DO_BUILD) {
            $prepend .= 'cd ' . $tokens['build_path'] . PHP_EOL;
        }

        $user = $server->user;
        if ($step->isCustom()) {
            $user = empty($step->command->user) ? $server->user : $step->command->user;
        }

        // Now get the full script
        return $this->getScriptForStep($step, $log, $tokens)
                    ->prependScript($prepend)
                    ->setServer($server, $this->private_key, $user);
    }

    /**
     * Gets the script which is used for the supplied step.
     *
     * @param TaskStep $step
     * @param ServerLog  $log
     * @param array      $tokens
     */
    private function getScriptForStep(TaskStep $step, ServerLog $log, array $tokens = [])
    {
        switch ($step->stage) {
            case Stage::DO_CLONE:
                return new Process('deploy.steps.CreateNewRelease', $tokens);
            case Stage::DO_INSTALL:
                // Write configuration file to release dir and symlink shared files.
                $process = new Process('deploy.steps.InstallNewRelease', $tokens);
                $process->prependScript($this->configurationFileCommands($log, $tokens['release_path']))
                        ->appendScript($this->sharedFileCommands($tokens['release_path'], $tokens['shared_path']));

                return $process;
            case Stage::DO_ACTIVATE:
                return new Process('deploy.steps.ActivateNewRelease', $tokens);
            case Stage::DO_PURGE:
                return new Process('deploy.steps.PurgeOldReleases', $tokens);

            //Build
            case Stage::DO_PREPARE:
                return new Process('build.steps.Prepare', $tokens);
            case Stage::DO_BUILD:
                return new Process('build.steps.Build', $tokens);
            case Stage::DO_TEST:
                return new Process('build.steps.test', $tokens);
            case Stage::DO_RESULT:
                return new Process('build.steps.result', $tokens);
        }

        // Custom step
        return new Process($step->command->script, $tokens, Process::DIRECT_INPUT);
    }

    /**
     * Fetchs a remote file from server.
     *
     * @param  string           $local_file
     * @param  string           $remote_file
     * @param  ServerLog        $log
     * @throws RuntimeException
     */
    private function fetchFile($remote_file, $local_file, ServerLog $log)
    {
        $process = new Process('deploy.FetchFileFromServer', [
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
     * Sends a file to a remote server.
     *
     * @param  string           $local_file
     * @param  string           $remote_file
     * @param  ServerLog        $log
     * @throws RuntimeException
     */
    private function sendFile($local_file, $remote_file, ServerLog $log)
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
     * Send a string to server.
     *
     * @param string    $remote_path
     * @param string    $content
     * @param ServerLog $log
     */
    private function sendFileFromString($remote_path, $content, ServerLog $log)
    {
        $tmp_file = tempnam(storage_path('app/'), 'tmpfile');
        file_put_contents($tmp_file, $content);

        // Upload the file
        $this->sendFile($tmp_file, $remote_path, $log);

        unlink($tmp_file);
    }

    /**
     * create the command for sending uploaded files.
     *
     * @param ServerLog $log
     * @param string    $release_dir
     */
    private function configurationFileCommands(ServerLog $log, $release_dir)
    {
        if (!$log->environment->configFiles->count()) {
            return '';
        }

        $parser = new ScriptParser;

        $script = '';

        foreach ($log->environment->configFiles as $file) {
            $script .= $parser->parseFile('deploy.ConfigurationFile', [
                'path' => $release_dir . '/' . $file->path,
            ]);
        }

        return $script . PHP_EOL;
    }

    /**
     * create the command for shared files.
     *
     * @param string $release_dir
     * @param string $shared_dir
     */
    private function sharedFileCommands($release_dir, $shared_dir)
    {
        if (!$this->project->sharedFiles->count()) {
            return '';
        }

        $parser = new ScriptParser;

        $script = '';

        foreach ($this->project->sharedFiles as $filecfg) {
            $pathinfo = pathinfo($filecfg->file);
            $template = 'File';

            $file = $filecfg->file;

            if (substr($file, 0, 1) === '/') {
                $file = substr($file, 1);
            }

            if (substr($file, -1) === '/') {
                $template      = 'Directory';
                $file          = substr($file, 0, -1);
            }

            if (isset($pathinfo['extension'])) {
                $filename = $pathinfo['filename'] . '.' . $pathinfo['extension'];
            } else {
                $filename = $pathinfo['filename'];
            }

            $script .= $parser->parseFile('deploy.Share' . $template, [
                'target_file' => $release_dir . '/' . $file,
                'source_file' => $shared_dir . '/' . $filename,
            ]);
        }

        return PHP_EOL . $script;
    }

    /**
     * Generates the list of tokens for the scripts.
     *
     * @param TaskStep $step
     * @param Server     $server
     */
    private function getTokenList(TaskStep $step, Server $server)
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
            $deployer_email = '';
            $deployer_name  = 'webhook';
            if ($this->task->user) {
                $deployer_name  = $this->task->user->name;
                $deployer_email = $this->task->user->email;
            } elseif ($this->task->is_webhook && !empty($this->task->source)) {
                $deployer_name = $this->task->source;
            }

            $tokens = array_merge($tokens, [
                'release'         => $this->task->release_id,
                'release_path'    => $releases_dir . '/' . $this->task->release_id,
                'deployer_email'  => $deployer_email,
                'deployer_name'   => $deployer_name,
            ]);
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
    private function logError($message)
    {
        return '<error>' . $message . '</error>';
    }
    /**
     * Generates an general output string to log to the DB.
     *
     * @param string $message
     */
    private function logSuccess($message)
    {
        return '<info>' . $message . '</info>';
    }
}
