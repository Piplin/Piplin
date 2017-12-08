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

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Piplin\Services\Scripts\Runner as Process;
use RuntimeException;

/**
 * Job to generate SSH key.
 */
class GenerateKeyJob extends Job
{
    use Dispatchable, SerializesModels;

    /**
     * @var Mixed
     */
    private $target;

    /**
     * Create a new job instance.
     *
     * @param Mixed $target
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (empty($this->target->private_key)) {
            $this->generateSSHKey();
        } elseif (empty($this->target->public_key)) {
            $this->regeneratePublicKey();
        }
    }

    /**
     * Generates a new SSH key and sets the private/public key properties.
     *
     * @return void
     */
    private function generateSSHKey()
    {
        $key_file = tempnam(storage_path('app/'), 'sshkey');

        $process = new Process('tools.GenerateSSHKey', [
            'key_file' => $key_file,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            unlink($key_file);
            throw new \RuntimeException($process->getErrorOutput());
        }

        $this->target->private_key = file_get_contents($key_file);
        $this->target->public_key  = file_get_contents($key_file . '.pub');

        unlink($key_file);
        unlink($key_file . '.pub');
    }

    /**
     * Regenerates a public key and sets the public key properties.
     *
     * @return void
     */
    private function regeneratePublicKey()
    {
        $key_file = tempnam(storage_path('app/'), 'sshkey');
        file_put_contents($key_file, $this->target->private_key);

        $process = new Process('tools.RegeneratePublicSSHKey', [
            'key_file' => $key_file,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $this->target->public_key  = file_get_contents($key_file . '.pub');

        unlink($key_file);
        unlink($key_file . '.pub');
    }
}
