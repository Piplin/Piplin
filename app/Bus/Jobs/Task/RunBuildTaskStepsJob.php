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
use Piplin\Models\Artifact;
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
 * Run steps of the build.
 */
class RunBuildTaskStepsJob extends BaseRunTaskStepsJob
{
    use SerializesModels, DispatchesJobs;

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
                // Only allow aborting if the build has not yet been tested
                if ($step->stage <= Stage::DO_TEST) {
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
        $remote_archive     = $this->project->clean_deploy_path . '/' . $this->release_archive;
        $local_archive      = storage_path('app/' . $this->release_archive);
        if ($step->stage === Stage::DO_PREPARE) {
            $this->sendFile($local_archive, $remote_archive, $log);
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
        $local_path = 'app/artifacts/build-' . $this->task->id . '/';
        foreach ($step->command->patterns as $pattern) {
            if (!$pattern || !$pattern->copy_pattern) {
                continue;
            }

            $this->fetchFile($latest_build_dir.'/'. $pattern->copy_pattern, $local_path, $log);
        }

        $this->saveArtifacts($local_path, $log);
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

        $prepend = '';
        // Make build_path as your current path
        if ($step->stage > Stage::DO_BUILD) {
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
            case Stage::DO_PREPARE:
                $process = new Process('build.steps.Prepare', $tokens);
                $process->appendScript($this->linkNewBuild($tokens));
                return $process;
            case Stage::DO_BUILD:
                return new Process('build.steps.Build', $tokens);
            case Stage::DO_TEST:
                return new Process('build.steps.Test', $tokens);
            case Stage::DO_RESULT:
                return new Process('build.steps.Result', $tokens);
        }

        // Custom step
        return new Process($step->command->script, $tokens, Process::DIRECT_INPUT);
    }

    /**
     * Fetchs a remote file from server.
     *
     * @param  string           $local_path
     * @param  string           $remote_file
     * @param  ServerLog        $log
     * @throws RuntimeException
     */
    private function fetchFile($remote_file, $local_path, ServerLog $log)
    {
        $process = new Process('deploy.FetchFileFromServer', [
            'port'        => $log->server->port,
            'private_key' => $this->private_key,
            'local_file'  => storage_path($local_path),
            'remote_file' => $remote_file,
            'username'    => $log->server->user,
            'ip_address'  => $log->server->ip_address,
        ]);
        $preLog = $log->output;

        $output = '';
        $process->run(function ($type, $output_line) use (&$output, &$log, $preLog, $local_path) {
            if ($type === \Symfony\Component\Process\Process::ERR) {
                $output .= $this->logError($output_line);
            } else {
                $output .= $this->logSuccess($output_line);
            }

            $log->output = $preLog . $output;
            $log->save();
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }

    /**
     * Saves the artifacts.
     *
     * @param string    $output_line
     * @param string    $local_path
     * @param ServerLog $log
     */
    private function saveArtifacts($local_path, $log)
    {
        preg_match_all("/Receiving (.*)/", $log->output, $matches);
        foreach ($matches[1] as $file) {
            $file = trim($file);
            $filePath = storage_path($local_path . $file);
            if (!file_exists($filePath)) {
                continue;
            }
           
            Artifact::create([
                'file_name'     => $file,
                'file_size'     => filesize($filePath),
                'mime'          => finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filePath),
                'task_id'       => $this->task->id,
                'server_log_id' => $log->id,
            ]);
        }
    }

    /**
     * Liknks the new build.
     *
     * @param array    $tokens
     *
     * @return string
     */
    private function linkNewBuild($tokens)
    {
        $parser = new ScriptParser;

        $script = $parser->parseFile('build.LinkNewBuild', $tokens);

        return PHP_EOL . $script;
    }
}
