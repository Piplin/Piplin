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

use Fixhub\Bus\Jobs\TestServerConnection;
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
     * @param  StoreServerRequest $request
     * @return Response
     */
    public function store(StoreServerRequest $request)
    {
        $fields = $request->only(
            'name',
            'user',
            'ip_address',
            'port',
            'path',
            'project_id',
            'deploy_code',
            'add_commands'
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

        $add_commands = false;
        if (isset($fields['add_commands'])) {
            $add_commands = $fields['add_commands'];
            unset($fields['add_commands']);
        }

        $server = Server::create($fields);

        // Add the server to the existing commands
        if ($add_commands) {
            foreach ($server->project->commands as $command) {
                $command->servers()->attach($server->id);
            }
        }

        return $server;
    }

    /**
     * Update the specified server in storage.
     *
     * @param  StoreServerRequest $request
     * @return Response
     */
    public function update($server_id, StoreServerRequest $request)
    {
        $server = Server::findOrFail($server_id);

        $server->update($request->only(
            'name',
            'user',
            'ip_address',
            'port',
            'path',
            'project_id',
            'deploy_code'
        ));

        return $server;
    }

    /**
     * Queues a connection test for the specified server.
     *
     * @param  int      $server_id
     * @return Response
     */
    public function test($server_id)
    {
        $server = Server::findOrFail($server_id);

        if (!$server->isTesting()) {
            $server->status = Server::TESTING;
            $server->save();

            dispatch(new TestServerConnection($server));
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Re-generates the order for the supplied servers.
     *
     * @param  Request  $request
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
     * @param  int      $server_id
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
