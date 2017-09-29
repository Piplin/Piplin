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

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Fixhub\Models\Key;
use Fixhub\Services\Scripts\Runner as Process;
use RuntimeException;

/**
 * Job to generate SSH key.
 */
class GenerateKeyJob extends Job
{
    use Dispatchable, SerializesModels;

    /**
     * @var Key
     */
    private $key;

    /**
     * Create a new job instance.
     *
     * @param Key $key
     */
    public function __construct(Key $key)
    {
        $this->key = $key;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (empty($this->key->private_key)) {
            $this->generateSSHKey();
        } elseif (empty($this->key->public_key)) {
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
        unlink($key_file);

        $process = new Process('tools.GenerateSSHKey', [
            'key_file' => $key_file,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $this->key->private_key = file_get_contents($key_file);
        $this->key->public_key  = file_get_contents($key_file . '.pub');

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
        file_put_contents($key_file, $this->private_key);

        $process = new Process('tools.RegeneratePublicSSHKey', [
            'key_file' => $key_file,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $this->key->public_key  = file_get_contents($key_file . '.pub');

        unlink($key_file);
        unlink($key_file . '.pub');
    }
}
