<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Bus\Jobs\UpdateGitMirror;
use Fixhub\Models\Command;
use Fixhub\Models\Deployment;
use Fixhub\Models\Project;
use Fixhub\Models\ServerLog;
use Illuminate\Http\Request;

/**
 * The controller of repository.
 */
class RepositoryController extends Controller
{

    /**
     * Handles incoming requests to refresh repository.
     *
     * @param  Request  $request
     * @param  string   $hash
     * @return Response
     */
    public function refresh(Request $request, $project_id)
    {
        $success = false;

        $project = Project::findOrFail($project_id);

        dispatch(new UpdateGitMirror($project));

        $success = true;

        return [
            'success' => $success,
            'last_mirrored' => $project->last_mirrored->toDateTimeString(),
        ];
    }
}
