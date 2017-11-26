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
use Piplin\Http\Requests\StoreReleaseRequest;
use Piplin\Models\Release;

/**
 * The controller of releases.
 */
class ReleaseController extends Controller
{
    /**
     * Adds a release for the specified task.
     *
     * @param StoreReleaseRequest $request
     *
     * @return Response
     */
    public function store(StoreReleaseRequest $request)
    {
        $fields = $request->only(
            'name',
            'project_id',
            'task_id'
        );

        $release = Release::create($fields);

        return [
            'success' => true,
        ];
    }
}
