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
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Piplin\Models\ConfigFile;
use Piplin\Models\Environment;
use Piplin\Models\Server;
use Piplin\Services\Scripts\Runner as Process;

/**
 * Sync config file to specified environments.
 */
class SyncConfigFileJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 100;

    private $configFile;

    private $project;

    private $private_key;

    private $environmentIds;

    private $postCommands;

    /**
     * Create a new command instance.
     *
     * @param ConfigFile $configFile
     * @param array      $environmentIds
     * @param string     $postCommands
     *
     * @return SyncConfigFileJob
     */
    public function __construct(ConfigFile $configFile, array $environmentIds, $postCommands)
    {
        $this->configFile = $configFile;
        $this->project = $configFile->targetable->project;
        $this->environmentIds = $environmentIds;
        $this->postCommands = $postCommands;

        $this->private_key = tempnam(storage_path('app/'), 'sshkey');
        file_put_contents($this->private_key, $this->project->private_key_content);
        chmod($this->private_key, 0600);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $environments = Environment::whereIn('id', $this->environmentIds)->get();

        $tmp_file = tempnam(storage_path('app/'), 'tmpfile');
        file_put_contents($tmp_file, $this->configFile->content);

        try {
            foreach ($environments as $environment) {
                $this->sendServerConfig($environment, $tmp_file);
            }
            $this->configFile->status = ConfigFile::SUCCESSFUL;
            $this->configFile->output = null;
        } catch (\Exception $error) {
            $this->configFile->status = ConfigFile::FAILED;
            $this->configFile->output = $error->getMessage();
        }
        $this->configFile->last_run = Carbon::now();

        $this->configFile->save();
    }

    private function sendServerConfig(Environment $environment, $local_file)
    {
        $servers = [];
        foreach ($environment->cabinets->pluck('servers')->flatten() as $server) {
            if (!$server->enabled) {
                continue;
            }
            $servers[] = $server;
        }

        foreach ($environment->servers->where('enabled', true) as $server) {
            $servers[] = $server;
        }

        foreach ($servers as $server) {
            $this->sendFileFromString($server, $local_file);
        }
    }

    /**
     * Send a string to server. fix me.
     *
     * @param Server $server
     */
    private function sendFileFromString(Server $server, $local_file)
    {
        $latest_release_dir = $this->project->clean_deploy_path . '/current';
        $process = new Process('deploy.SendFileToServer', [
            'port'        => $server->port,
            'private_key' => $this->private_key,
            'local_file'  => $local_file,
            'remote_file' => $latest_release_dir . '/'. $this->configFile->path,
            'username'    => $server->user,
            'ip_address'  => $server->ip_address,
        ]);

        $append = 'cd ' . $latest_release_dir . PHP_EOL;

        if ($this->postCommands) {
            $append .= $this->postCommands . PHP_EOL;
        }

        $process->appendScript($append);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        error_log(var_export(file_get_contents($local_file), true), 3, '/tmp/gsl.log');
        // Upload the file
        //$this->sendFile($tmp_file, $remote_path, $log);
    }
}
