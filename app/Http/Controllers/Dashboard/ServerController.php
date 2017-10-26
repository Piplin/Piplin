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

use Illuminate\Http\Request;
use Fixhub\Bus\Jobs\TestServerConnectionJob;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreServerRequest;
use Fixhub\Models\Server;
use Fixhub\Models\Project;

/**
 * Server management controller.
 */
class ServerController extends Controller
{
    /**
     * Store a newly created server in storage.
     *
     * @param Project $project
     * @param StoreServerRequest $request
     *
     * @return Response
     */
    public function store(Project $project, StoreServerRequest $request)
    {
        $fields = $request->only(
            'name',
            'enabled',
            'user',
            'ip_address',
            'port',
            'path',
            'environment_id'
        );

        // Get the current highest server order
        $max = Server::where('environment_id', $fields['environment_id'])
                           ->orderBy('order', 'DESC')
                           ->first();

        $order = 0;
        if (isset($max)) {
            $order = $max->order + 1;
        }

        $fields['order'] = $order;
        $fields['output'] = null;

        $server = Server::create($fields);

        return $server;
    }

    /**
     * Update the specified server in storage.
     *
     * @param Project $project
     * @param Server $server
     * @param StoreServerRequest $request
     *
     * @return Response
     */
    public function update(Project $project, Server $server, StoreServerRequest $request)
    {
        $server->update($request->only(
            'name',
            'enabled',
            'user',
            'ip_address',
            'port',
            'path',
            'environment_id'
        ));

        return $server;
    }

    /**
     * Queues a connection test for the specified server.
     *
     * @param Project $project
     * @param Server $server
     *
     * @return Response
     */
    public function test(Project $project, Server $server)
    {
        if (!$server->isTesting()) {
            $server->status = Server::TESTING;
            $server->save();

            dispatch(new TestServerConnectionJob($server));
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Re-generates the order for the supplied servers.
     *
     * @param Project $project
     * @param Request $request
     *
     * @return Response
     */
    public function reorder(Project $project, Request $request)
    {
        $order = 0;

        foreach ($request->get('servers') as $server_id) {
            $server = Server::findOrFail($server_id);
            $server->update([
                'order' => $order,
            ]);

            $order++;
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Remove the specified server from storage.
     *
     * @param Project $project
     * @param Server $server
     *
     * @return Response
     */
    public function destroy(Project $project, Server $server)
    {
        $server->forceDelete();

        return [
            'success' => true,
        ];
    }
}
