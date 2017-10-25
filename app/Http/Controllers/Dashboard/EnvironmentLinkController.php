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

use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreEnvironmentLinkRequest;
use Fixhub\Models\Environment;

/**
 * Controller for managing notifications.
 */
class EnvironmentLinkController extends Controller
{
    /**
     * Store a newly created notification in storage.
     *
     * @param StoreEnvironmentLinkRequest $request
     *
     * @return Response
     */
    public function store(StoreEnvironmentLinkRequest $request)
    {
        $fields = $request->only(
            'environment_id',
            'link_type',
            'environments'
        );

        $environment = Environment::findOrFail($fields['environment_id']);

        $data = [];
        foreach ($fields['environments'] as $id) {
            $data[$id] = ['link_type' => $fields['link_type']];
        }

        $environment->oppositeEnvironments()->sync($data);

        // Trigger
        return $environment->oppositeEnvironments;
    }
}
