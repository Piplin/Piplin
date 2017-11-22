<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Piplin\Http\Controllers\Controller;
use Piplin\Models\Project;

/**
 * The project controller.
 */
class ProjectController extends Controller
{
    /**
     * The details of an individual project.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function show(Request $request)
    {
        $project_id = $request->get('project_id');

        $project = Project::findOrFail($project_id);

        return $project;
    }
}
