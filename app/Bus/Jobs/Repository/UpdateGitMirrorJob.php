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

use Carbon\Carbon;
use Fixhub\Bus\Jobs\UpdateGitReferencesJob;
use Fixhub\Bus\Jobs\Job;
use Fixhub\Models\Project;
use Fixhub\Services\Scripts\Parser as ScriptParser;
use Fixhub\Services\Scripts\Runner as Process;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Queue;

/**
 * Updates the git mirror for a project.
 */
class UpdateGitMirrorJob extends Job
{
    use SerializesModels, DispatchesJobs;

    /**
    * @var int
    */
    public $timeout = 0;

    /**
    * @var Project
    */
    private $project;

    /**
     * Create a new job instance.
     *
     * @param Project $project
     *
     * @return void
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $private_key = tempnam(storage_path('app/'), 'sshkey');
        file_put_contents($private_key, $this->project->private_key_content);

        $wrapper = with(new ScriptParser)->parseFile('tools.SSHWrapperScript', [
            'private_key' => $private_key,
        ]);

        $wrapper_file = tempnam(storage_path('app/'), 'gitssh');
        file_put_contents($wrapper_file, $wrapper);

        $process = new Process('tools.MirrorGitRepository', [
            'wrapper_file' => $wrapper_file,
            'mirror_path'  => $this->project->mirrorPath(),
            'repository'   => $this->project->repository,
        ]);
        $process->run();

        unlink($wrapper_file);
        unlink($private_key);

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Could not mirror repository - ' . $process->getErrorOutput());
        }

        $this->project->last_mirrored = Carbon::now();
        $this->project->save();

        $this->dispatch(new UpdateGitReferencesJob($this->project));
    }
}
