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

use Fixhub\Models\Server;
use Fixhub\Models\Environment;
use Fixhub\Models\Cabinet;
use Fixhub\Services\Scripts\Runner as Process;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Tests if a server can successfully be SSHed into.
 */
class TestServerConnectionJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
    * @var int
    */
    public $timeout = 10;

    /**
    * @var Server
    */
    public $server;

    /**
     * Create a new command instance.
     *
     * @param Server $server
     *
     * @return TestServerConnectionJob
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->server->status = Server::TESTING;
        $this->server->output = null;
        $this->server->save();

        // Fix me please
        $deploy_path = $this->server->clean_path;
        if ($this->server->targetable instanceof Cabinet) {
            $private_key = $this->server->targetable->private_key_content;
        } else {
            $private_key = $this->server->targetable->targetable->private_key_content;
            if ($this->server->targetable->targetable->clean_deploy_path) {
                 $deploy_path = $this->server->targetable->targetable->clean_deploy_path;
            }
        }

        if (empty($private_key)) {
            $this->server->status = Server::FAILED;
            $this->server->output = trans('keys.ssh_key_empty');
            $this->server->save();
            return;
        }

        $key = tempnam(storage_path('app/'), 'sshkey');
        file_put_contents($key, $private_key);

        try {
            $process = new Process('TestServerConnection', [
                'project_path'   => $deploy_path,
                'test_file'      => time() . '_testing_fixhub.txt',
                'test_directory' => time() . '_testing_fixhub_dir',
            ]);

            $process->setServer($this->server, $key)
                    ->run();

            if (!$process->isSuccessful()) {
                $this->server->status = Server::FAILED;
                $this->server->output = $process->getErrorOutput();
            } else {
                $this->server->status = Server::SUCCESSFUL;
                $this->server->output = null;
            }
        } catch (\Exception $error) {
            $this->server->status = Server::FAILED;
        }

        $this->server->save();

        unlink($key);
    }
}
