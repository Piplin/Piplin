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

use Piplin\Models\Command;
use Piplin\Models\ConfigFile;
use Piplin\Models\DeployTemplate;
use Piplin\Models\Project;
use Piplin\Models\SharedFile;
use Piplin\Models\Variable;

/**
 * A class to handle cloning between template and project.
 */
class SetupSkeletonJob extends Job
{
    /**
     * @var mixed
     */
    private $target;

    /**
     * @var mixed
     */
    private $skeleton;

    /**
     * Create a new command instance.
     *
     * @param mixed $target
     * @param mixed $skeleton
     *
     * @return SetupSkeletonJob
     */
    public function __construct($target, $skeleton)
    {
        $this->target   = $target;
        $this->skeleton = $skeleton;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->skeleton) {
            return;
        }

        if (!$this->skeleton instanceof DeployTemplate && !$this->skeleton instanceof Project) {
            return;
        }

        foreach ($this->skeleton->commands as $command) {
            $data = $command->toArray();

            $this->target->commands()->create($data);
        }

        foreach ($this->skeleton->environments as $environment) {
            $data = $environment->toArray();

            $this->target->environments()->create($data);
        }

        foreach ($this->skeleton->variables as $variable) {
            $data = $variable->toArray();

            $this->target->variables()->create($data);
        }

        foreach ($this->skeleton->sharedFiles as $file) {
            $data = $file->toArray();

            $this->target->sharedFiles()->create($data);
        }

        foreach ($this->skeleton->configFiles as $file) {
            $data = $file->toArray();

            $this->target->configFiles()->create($data);
        }
    }
}
