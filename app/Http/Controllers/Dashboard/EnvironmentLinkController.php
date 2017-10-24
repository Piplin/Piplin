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
     * @param StoreHookRequest $request
     *
     * @return Response
     */
    public function store(StoreEnvironmentLinkRequest $request)
    {
        $fields = $request->only(
            'environment_id',
            'link_id',
            'environments'
        );

        $environment = Environment::findOrFail($fields['environment_id']);

        $data = [];

        foreach($fields['environments'] as $id) {
            $data[$id] = ['link_id' => $fields['link_id']];
        }

        $environment->opposite_environments()->sync($data);
        var_dump($fields);
    }
}
