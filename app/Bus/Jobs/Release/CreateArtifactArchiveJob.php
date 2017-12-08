<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Jobs\Release;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Piplin\Models\Task;
use Piplin\Models\Release;
use Piplin\Services\Scripts\Runner as Process;
use RuntimeException;

/**
 * Job to create the archive.
 */
class CreateArtifactArchiveJob
{
    use Dispatchable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 0;

    /**
     * @var Task
     */
    private $task;

    /**
     * @var Release
     */
    private $release;

    /**
     * @var string
     */
    private $path;

    /**
     * Create a new job instance.
     *
     * @param Task    $task
     * @param Release $release
     * @param string  $path
     */
    public function __construct(Task $task, Release $release, $path)
    {
        $this->task     = $task;
        $this->release  = $release;
        $this->path     = $path;
    }

    /**
     * Execute the job.
     *
     * @throws \RuntimeException
     */
    public function handle()
    {
        $process = new Process('deploy.CreateArtifactArchive', [
            'artifact_path'   => storage_path('app/artifacts/build-' . $this->release->task->id . '/'),
            'files'           => $this->release->artifact_names,
            'release_archive' => storage_path('app/' . $this->path),
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Could not package artifact - ' . $process->getErrorOutput());
        }
    }
}
