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
use Fixhub\Bus\Events\DeployFinishedEvent;
use Fixhub\Bus\Jobs\UpdateGitMirrorJob;
use Fixhub\Models\Command as Stage;
use Fixhub\Models\Deployment;
use Fixhub\Models\DeployStep;
use Fixhub\Models\Project;
use Fixhub\Models\Server;
use Fixhub\Models\ServerLog;
use Fixhub\Models\User;
use Fixhub\Models\Environment;
use Fixhub\Services\Scripts\Parser as ScriptParser;
use Fixhub\Services\Scripts\Runner as Process;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Queue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

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

    private $private_key;

    /**
     * @var string
     */
    private $cache_key;

    /**
     * @var string
     */
    private $release_archive;

    /**
     * Create a new command instance.
     *
     * @param  Deployment    $deployment
     */
    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
        $this->project = $deployment->project;
        $this->cache_key  = AbortDeploymentJob::CACHE_KEY_PREFIX . $deployment->id;
    }

    /**
     * Overwrite the queue method to push to a different queue.
     *
     * @param  Queue         $queue
     * @param  DeployProjectJob $command
     */
    public function queue(Queue $queue, $command)
    {
        $queue->pushOn('fixhub-high', $command);
    }

    /**
     * Execute the command.
     */
    public function handle()
    {
        $this->deployment->started_at = Carbon::now();
        $this->deployment->status     = Deployment::DEPLOYING;
        $this->deployment->save();

        $this->project->status = Project::DEPLOYING;
        $this->project->save();

        $this->private_key = tempnam(storage_path('app/'), 'sshkey');
        file_put_contents($this->private_key, $this->project->key->private_key);

        $this->release_archive = $this->project->id . '_' . $this->deployment->release_id . '.tar.gz';

        try {
            $this->dispatch(new UpdateGitMirrorJob($this->project));
            // If the build has been manually triggered get the committer info from the repo
            $this->updateRepoInfo();

            $this->createReleaseArchive();

            foreach ($this->deployment->steps as $step) {
                $this->runStep($step);
            }

            $this->deployment->status          = Deployment::COMPLETED;
            $this->project->status = Project::FINISHED;
        } catch (\Exception $error) {
            $this->deployment->status          = Deployment::FAILED;
            $this->project->status = Project::FAILED;

            if ($error->getMessage() === 'Cancelled') {
                $this->deployment->status = Deployment::ABORTED;
            }

            $this->cancelPendingSteps($this->deployment->steps);

            if (isset($step)) {
                // Cleanup the release if it has not been activated
                if ($step->stage <= Stage::DO_ACTIVATE) {
                    $this->cleanupDeployment();
                } else {
                    $this->deployment->status          = Deployment::COMPLETED_WITH_ERRORS;
                    $this->project->status = Project::FINISHED;
                }
            }
        }

        if ($this->deployment->status !== Deployment::ABORTED) {
            $this->deployment->finished_at =  Carbon::now();
        }

        $this->deployment->save();

        $this->project->last_run = $this->deployment->finished_at;
        $this->project->save();

        $this->updateEnvironmentsInfo();

        // Notify user or others the deployment has been finished
        event(new DeployFinishedEvent($this->deployment));

        unlink($this->private_key);

        if (file_exists(storage_path('app/' . $this->release_archive))) {
            unlink(storage_path('app/' . $this->release_archive));
        }
    }

    /**
     * Clones the repository locally to get the latest log entry and updates
     * the deployment model.
     */
    private function updateRepoInfo()
    {
        $commit = ($this->deployment->commit === Deployment::LOADING ? null : $this->deployment->commit);

        $process = new Process('tools.GetCommitDetails', [
            'deployment'    => $this->deployment->id,
            'mirror_path'   => $this->project->mirrorPath(),
            'git_reference' => $commit ?: $this->deployment->branch,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->deployment->output = $process->getErrorOutput();
            throw new \RuntimeException('Could not get repository info');
        }

        $git_info = $process->getOutput();

        list($commit, $committer, $email) = explode("\x09", $git_info);

        $this->deployment->commit          = $commit;
        $this->deployment->committer       = trim($committer);
        $this->deployment->committer_email = trim($email);

        //$process = new Process('git symbolic-ref --short -q HEAD');

        if (!$this->deployment->user_id && !$this->deployment->source) {
            $user = User::where('email', $this->deployment->committer_email)->first();

            if ($user) {
                $this->deployment->user_id = $user->id;
            }
        }
    }

    /**
     * Creates the archive for the commit to deploy.
     */
    private function createReleaseArchive()
    {
        $process = new Process('deploy.CreateReleaseArchive', [
            'mirror_path'     => $this->project->mirrorPath(),
            'sha'             => $this->deployment->commit,
            'release_archive' => storage_path('app/' . $this->release_archive),
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Could not get repository info - ' . $process->getErrorOutput());
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
                'project_path'   => $server->clean_path,
                'release_path'   => $server->clean_path . '/releases/' . $this->deployment->release_id,
                'remote_archive' => $server->clean_path . '/' . $this->release_archive,
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
        $latest_release_dir = $log->server->clean_path . '/releases/' . $this->deployment->release_id;
        $remote_archive     = $log->server->clean_path . '/' . $this->release_archive;
        $local_archive      = storage_path('app/' . $this->release_archive);

        if ($step->stage === Stage::DO_CLONE) {
            $this->sendFile($local_archive, $remote_archive, $log);
        } elseif ($step->stage === Stage::DO_INSTALL) {
            $this->sendConfigFileFromString($latest_release_dir, $log);
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
     * @param  DeployStep $step
     * @param  Server     $server
     */
    private function buildScript(DeployStep $step, Server $server, ServerLog $log)
    {
        $tokens = $this->getTokenList($step, $server);

        // Generate the export
        $exports = '';
        foreach ($this->project->variables as $variable) {
            $key   = $variable->name;
            $value = $variable->value;

            $exports .= "export {$key}={$value}" . PHP_EOL;
        }

        $user = $server->user;
        if ($step->isCustom()) {
            $user = empty($step->command->user) ? $server->user : $step->command->user;
        }

        // Now get the full script
        return $this->getScriptForStep($step, $log, $tokens)
                    ->prependScript($exports)
                    ->setServer($server, $this->private_key, $user);
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

    /**
     * Gets the script which is used for the supplied step.
     *
     * @param DeployStep $step
     * @param Server $server
     * @param array $tokens
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
        $releases_dir       = $server->clean_path . '/releases';
        $latest_release_dir = $releases_dir . '/' . $this->deployment->release_id;
        $release_shared_dir = $server->clean_path . '/shared';
        $remote_archive     = $server->clean_path . '/' . $this->release_archive;

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
            'project_path'    => $server->clean_path,
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
     * Update the status and last run time of the deployment enviroments.
     */
    private function updateEnvironmentsInfo()
    {
        foreach ($this->deployment->environments as $environment) {
            $environment->last_run = $this->project->last_run;
            $environment->status = $this->project->status;
            $environment->save();
        }
    }
}
