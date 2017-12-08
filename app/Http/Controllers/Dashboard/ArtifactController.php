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

use Illuminate\Contracts\Routing\ResponseFactory;
use Piplin\Http\Controllers\Controller;
use Piplin\Models\Artifact;
use Piplin\Models\Project;

/**
 * Controller for managing artifacts.
 */
class ArtifactController extends Controller
{
    /**
     * Downloads an artifact file for the supplied id.
     *
     * @param Project         $project
     * @param Artifact        $artifact
     * @param ResponseFactory $response
     *
     * @return ResponseFactory
     */
    public function download(Project $project, Artifact $artifact, ResponseFactory $response)
    {
        $file = storage_path('app/artifacts/build-'.$artifact->task_id.'/'.$artifact->file_name);

        return $response->download($file);
    }
}
