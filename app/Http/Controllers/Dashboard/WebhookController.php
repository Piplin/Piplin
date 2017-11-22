<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Dashboard;

use Piplin\Http\Controllers\Controller;
use Piplin\Models\Project;

/**
 * The deployment webhook management controller.
 */
class WebhookController extends Controller
{
    /**
     * Generates a new webhook URL.
     *
     * @param Project $project
     *
     * @return Response
     */
    public function refresh(Project $project)
    {
        $project->generateHash();
        $project->save();

        return [
            'url' => route('webhook.deploy', $project->hash),
        ];
    }
}
