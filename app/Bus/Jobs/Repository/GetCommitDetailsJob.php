<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Jobs\Repository;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Fixhub\Services\Scripts\Runner as Process;
use Fixhub\Models\Project;
use RuntimeException;
use Closure;

/**
 * Clones the repository locally to get the latest log entry.
 */
class GetCommitDetailsJob
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
     * @var Closure
     */
    private $callback;

    /**
     * Create a new job instance.
     *
     * @param Project $project
     */
    public function __construct(Project $project, $commit, Closure $callback = null)
    {
        $this->project = $project;
        $this->commit = $commit;
        $this->callback = $callback ?: function () {
        };
    }

    /**
     * Execute the job.
     *
     * @throws \RuntimeException
     */
    public function handle()
    {
        $process = new Process('tools.GetCommitDetails', [
            'mirror_path'   => $this->project->mirrorPath(),
            'git_reference' => $this->commit,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Could not get repository info');
        }

        $git_info = $process->getOutput();

        return ($this->callback)($git_info);
    }
}
