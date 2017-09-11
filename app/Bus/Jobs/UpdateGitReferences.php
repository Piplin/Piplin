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

use Fixhub\Models\Project;
use Fixhub\Models\Ref;
use Fixhub\Services\Scripts\Runner as Process;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Updates the list of tags and branches in a project.
 */
class UpdateGitReferences extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $project;

    /**
     * Create a new job instance.
     *
     * @param  Project $project
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
        $mirror_dir = $this->project->mirrorPath();

        $this->project->refs()->delete();

        foreach (['tag', 'branch'] as $ref) {
            $process = new Process('tools.ListGitReferences', [
                'mirror_path'   => $mirror_dir,
                'git_reference' => $ref,
            ]);
            $process->run();

            if ($process->isSuccessful()) {
                foreach (explode(PHP_EOL, trim($process->getOutput())) as $reference) {
                    $reference = trim($reference);

                    if (empty($reference)) {
                        continue;
                    }

                    if (substr($reference, 0, 1) === '*') {
                        $reference = trim(substr($reference, 1));
                    }

                    Ref::create([
                        'name'       => $reference,
                        'project_id' => $this->project->id,
                        'is_tag'     => ($ref === 'tag'),
                    ]);
                }
            }
        }
    }
}
