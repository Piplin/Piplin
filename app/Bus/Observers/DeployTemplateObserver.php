<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Observers;

use Fixhub\Models\DeployTemplate;

/**
 * Event observer for DeployTemplate model.
 */
class DeployTemplateObserver
{
    /**
     * Called when the model is deleting.
     *
     * @param DeployTemplate $template
     */
    public function deleting(DeployTemplate $template)
    {
        $template->variables()->forceDelete();
        $template->sharedFiles()->forceDelete();

        foreach ($template->commands as $command) {
            $command->environments()->detach();
        }

        foreach ($template->environments as $environment) {
            $environment->commands()->detach();
            $environment->configFiles()->detach();
        }

        $template->commands()->forceDelete();

        $template->environments()->forceDelete();
        $template->configFiles()->forceDelete();
    }
}
