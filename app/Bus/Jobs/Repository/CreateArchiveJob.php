<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Jobs\Repository;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Piplin\Models\Project;
use Piplin\Services\Scripts\Runner as Process;
use RuntimeException;

/**
 * Job to create the archive.
 */
class CreateArchiveJob
{
    use Dispatchable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 0;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var string
     */
    private $commit;

    /**
     * @var string
     */
    private $path;

    /**
     * Create a new job instance.
     *
     * @param Project $project
     * @param string  $commit
     * @param string  $path
     */
    public function __construct(Project $project, $commit, $path)
    {
        $this->project = $project;
        $this->commit  = $commit;
        $this->path    = $path;
    }

    /**
     * Execute the job.
     *
     * @throws \RuntimeException
     */
    public function handle()
    {
        $process = new Process('deploy.CreateReleaseArchive', [
            'mirror_path'     => $this->project->mirrorPath(),
            'sha'             => $this->commit,
            'release_archive' => storage_path('app/' . $this->path),
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Could not get repository info - ' . $process->getErrorOutput());
        }
    }
}
