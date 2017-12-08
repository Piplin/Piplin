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

use Closure;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Piplin\Models\Project;
use Piplin\Services\Scripts\Runner as Process;
use RuntimeException;

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
     * @param string  $commit
     * @param Closure $callback
     */
    public function __construct(Project $project, $commit, Closure $callback = null)
    {
        $this->project  = $project;
        $this->commit   = $commit;
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
