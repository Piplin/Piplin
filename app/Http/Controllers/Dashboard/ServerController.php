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

use Fixhub\Bus\Jobs\TestServerConnectionJob;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests;
use Fixhub\Http\Requests\StoreServerRequest;
use Fixhub\Models\Server;
use Illuminate\Http\Request;

/**
 * Server management controller.
 */
class ServerController extends Controller
{
    /**
     * Store a newly created server in storage.
     *
     * @param StoreServerRequest $request
     *
     * @return Response
     */
    public function store(StoreServerRequest $request)
    {
        $fields = $request->only(
            'name',
            'enabled',
            'user',
            'ip_address',
            'port',
            'path',
            'project_id',
            'environment_id',
            'deploy_code'
        );

        // Get the current highest server order
        $max = Server::where('project_id', $fields['project_id'])
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
     * @param int $server_id
     * @param StoreServerRequest $request
     *
     * @return Response
     */
    public function update($server_id, StoreServerRequest $request)
    {
        $server = Server::findOrFail($server_id);

        $server->update($request->only(
            'name',
            'enabled',
            'user',
            'ip_address',
            'port',
            'path',
            'project_id',
            'environment_id',
            'deploy_code'
        ));

        return $server;
    }

    /**
     * Queues a connection test for the specified server.
     *
     * @param int $server_id
     *
     * @return Response
     */
    public function test($server_id)
    {
        $server = Server::findOrFail($server_id);

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
     * @param Request $request
     *
     * @return Response
     */
    public function reorder(Request $request)
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
     * @param int $server_id
     *
     * @return Response
     */
    public function destroy($server_id)
    {
        $server = Server::findOrFail($server_id);

        $server->delete();

        return [
            'success' => true,
        ];
    }
}
