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
use Illuminate\Contracts\Routing\ResponseFactory;
use Fixhub\Http\Requests\StoreHookRequest;
use Symfony\Component\HttpFoundation\Response;
use Fixhub\Models\Hook;

/**
 * Controller for managing notifications.
 */
class HookController extends Controller
{
    /**
     * Store a newly created notification in storage.
     *
     * @param StoreHookRequest $request
     *
     * @return Response
     */
    public function store(StoreHookRequest $request)
    {
        $input = $request->only(
            'name',
            'project_id',
            'type',
            'enabled',
            'on_deployment_success',
            'on_deployment_failure'
        );

        $input['config'] = $request->configOnly();

        $hook = Hook::create($input);

        return $hook;
    }

    /**
     * Update the specified notification in storage.
     *
     * @param int $hook_id
     * @param StoreHookRequest $request
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($hook_id, StoreHookRequest $request)
    {
        $input = $request->only(
            'name',
            'enabled',
            'on_deployment_success',
            'on_deployment_failure'
        );

        $input['config'] = $request->configOnly();

        $hook = Hook::findOrFail($hook_id);

        $hook->update($input);

        return $hook;
    }

    /**
     * Remove the specified hook from storage.
     *
     * @param int $hook_id
     *
     * @return Response
     */
    public function destroy($hook_id)
    {
        $hook = Hook::findOrFail($hook_id);

        $hook->delete();

        return [
            'success' => true,
        ];
    }
}
