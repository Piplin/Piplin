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
        return [
            'success' => true,
        ];
    }
}
