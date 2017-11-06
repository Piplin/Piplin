<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Api;

use Fixhub\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Fixhub\Models\Project;

/**
 * The project controller.
 */
class ProjectController extends Controller
{
    public function show(Request $request)
    {
        $project_id = $request->get('project_id');

        $project = Project::findOrFail($project_id);

        return $project;
    }
}
