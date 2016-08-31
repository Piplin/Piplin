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

use Fixhub\Models\Command;
use Fixhub\Models\Project;
use Fixhub\Models\ConfigFile;
use Fixhub\Models\SharedFile;
use Fixhub\Models\Template;
use Fixhub\Models\Variable;

/**
 * A class to handle cloning the command templates for the project.
 */
class SetupProject extends Job
{
    private $project;
    private $template;

    /**
     * Create a new command instance.
     *
     * @param  Project      $project
     * @param  int          $template_id
     * @return SetupProject
     */
    public function __construct(Project $project, $template)
    {
        $this->project  = $project;
        $this->template = $template;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->template) {
            return;
        }

        foreach ($this->template->commands as $command) {
            $data = $command->toArray();

            $this->project->commands()->create($data);
        }

        foreach ($this->template->variables as $variable) {
            $data = $variable->toArray();

            $this->project->variables()->create($data);
        }

        foreach ($this->template->sharedFiles as $file) {
            $data = $file->toArray();

            $this->project->sharedFiles()->create($data);
        }

        foreach ($this->template->configFiles as $file) {
            $data = $file->toArray();

            $this->project->configFiles()->create($data);
        }
    }
}
