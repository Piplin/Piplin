<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Jobs\Deploy;

use Carbon\Carbon;
use Fixhub\Bus\Jobs\UpdateGitReferencesJob;
use Fixhub\Bus\Jobs\Job;
use Fixhub\Bus\Jobs\AbortDeploymentJob;
use Fixhub\Models\Deployment;
use Fixhub\Models\Command as Stage;
use Fixhub\Models\DeployStep;
use Fixhub\Models\Project;
use Fixhub\Models\Server;
use Fixhub\Models\ServerLog;
use Fixhub\Models\User;
use Fixhub\Models\Environment;
use Fixhub\Services\Scripts\Parser as ScriptParser;
use Fixhub\Services\Scripts\Runner as Process;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Cache;

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
     * @var Deployment
     */
    private $deployment;

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
     * Create a new job instance.
     *
     * @param Deployment $deployment
     * @param string     $private_key
     * @param string     $release_archive
     *
     * @return void
     */
    public function __construct(Deployment $deployment, $private_key, $release_archive)
    {
        $this->deployment = $deployment;
        $this->project = $deployment->project;
        $this->private_key = $private_key;
        $this->cache_key  = AbortDeploymentJob::CACHE_KEY_PREFIX . $deployment->id;
        $this->release_archive = $release_archive;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->deployment->steps as $step) {
            $this->runStep($step);
        }
    }

    /**
     * Executes the commands for a step.
     *
     * @param  DeployStep        $step
     * @throws \RuntimeException
     */
    private function runStep(DeployStep $step)
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
     * @param  DeployStep $step
     * @param  ServerLog  $log
     */
    private function sendFilesForStep(DeployStep $step, ServerLog $log)
    {
        $latest_release_dir = $this->project->clean_deploy_path . '/releases/' . $this->deployment->release_id;
        $remote_archive     = $this->project->clean_deploy_path . '/' . $this->release_archive;
        $local_archive      = storage_path('app/' . $this->release_archive);
        if ($step->stage === Stage::DO_CLONE) {
            $this->sendFile($local_archive, $remote_archive, $log);
        } elseif ($step->stage === Stage::DO_INSTALL) {
            $this->sendConfigFileFromString($latest_release_dir, $log);
        } elseif ($step->stage === Stage::DO_PURGE) {
            $this->fetchFile($latest_release_dir.'/../*.tar.gz', storage_path('app/artifacts/'), $log);
        }
    }

    /**
     * Sends the config files to the server.
     *
     * @param  string $release_dir
     * @param  ServerLog  $log
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
     * @param DeployStep $step
     * @param Server     $server
     * @param ServerLog  $log
     */
    private function buildScript(DeployStep $step, Server $server, ServerLog $log)
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
        if ($step->stage > Stage::DO_INSTALL) {
            $prepend .= "cd ". $tokens['release_path'] . PHP_EOL;
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
     * @param DeployStep $step
     * @param ServerLog  $log
     * @param array      $tokens
     */
    private function getScriptForStep(DeployStep $step, ServerLog $log, array $tokens = [])
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
                // Switching sent/received around
                $output_line = str_replace('received', 'xxx', $output_line);
                $output_line = str_replace('sent', 'received', $output_line);
                $output_line = str_replace('xxx', 'sent', $output_line);

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
                // Switching sent/received around
                $output_line = str_replace('received', 'xxx', $output_line);
                $output_line = str_replace('sent', 'received', $output_line);
                $output_line = str_replace('xxx', 'sent', $output_line);

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
     * @param  string    $remote_path
     * @param  string    $content
     * @param  ServerLog $log
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
     * @param string $release_dir
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
     * @param  string $release_dir
     * @param  string $shared_dir
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
     * @param  DeployStep $step
     * @param  Server     $server
     */
    private function getTokenList(DeployStep $step, Server $server)
    {
        $releases_dir       = $this->project->clean_deploy_path . '/releases';
        $release_shared_dir = $this->project->clean_deploy_path . '/shared';
        $remote_archive     = $this->project->clean_deploy_path . '/' . $this->release_archive;
        $latest_release_dir = $releases_dir . '/' . $this->deployment->release_id;

        // Set the fixhub tags
        $deployer_email = '';
        $deployer_name  = 'webhook';
        if ($this->deployment->user) {
            $deployer_name  = $this->deployment->user->name;
            $deployer_email = $this->deployment->user->email;
        } elseif ($this->deployment->is_webhook && !empty($this->deployment->source)) {
            $deployer_name = $this->deployment->source;
        }

        $tokens = [
            'release'         => $this->deployment->release_id,
            'release_path'    => $latest_release_dir,
            'project_path'    => $this->project->clean_deploy_path,
            'branch'          => $this->deployment->branch,
            'sha'             => $this->deployment->commit,
            'short_sha'       => $this->deployment->short_commit,
            'deployer_email'  => $deployer_email,
            'deployer_name'   => $deployer_name,
            'committer_email' => $this->deployment->committer_email,
            'committer_name'  => $this->deployment->committer,
        ];

        if (!$step->isCustom()) {
            $tokens = array_merge($tokens, [
                'remote_archive' => $remote_archive,
                'builds_to_keep' => $this->project->builds_to_keep + 1,
                'shared_path'    => $release_shared_dir,
                'releases_path'  => $releases_dir,
            ]);
        }

        return $tokens;
    }

    /**
     * Generates an error string to log to the DB.
     *
     * @param  string $message
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
