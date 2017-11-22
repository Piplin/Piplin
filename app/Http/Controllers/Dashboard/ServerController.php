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

use Illuminate\Http\Request;
use Piplin\Bus\Jobs\TestServerConnectionJob;
use Piplin\Http\Controllers\Controller;
use Piplin\Http\Requests\StoreServerRequest;
use Piplin\Models\Cabinet;
use Piplin\Models\Environment;
use Piplin\Models\Plan;
use Piplin\Models\Project;
use Piplin\Models\Server;

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
            'targetable_type',
            'targetable_id'
        );

        $targetable_id   = array_pull($fields, 'targetable_id');
        $targetable_type = array_pull($fields, 'targetable_type');

        if ($targetable_type === 'Piplin\\Models\\Environment') {
            $targetable = Environment::findOrFail($targetable_id);
        } elseif ($targetable_type === 'Piplin\\Models\\Plan') {
            $targetable = Plan::findOrFail($targetable_id);
        } else {
            $targetable = Cabinet::findOrFail($targetable_id);
        }

        // Get the current highest server order
        $max = Server::where('targetable_id', $targetable_id)
                           ->where('targetable_type', $targetable_type)
                           ->orderBy('order', 'DESC')
                           ->first();

        $order = 0;
        if (isset($max)) {
            $order = $max->order + 1;
        }

        $fields['order']  = $order;
        $fields['output'] = null;

        $server = $targetable->servers()->create($fields);

        return $server;
    }

    /**
     * Update the specified server in storage.
     *
     * @param Server             $server
     * @param StoreServerRequest $request
     *
     * @return Response
     */
    public function update(Server $server, StoreServerRequest $request)
    {
        $server->update($request->only(
            'name',
            'enabled',
            'user',
            'ip_address',
            'port',
            'targetable_id'
        ));

        return $server;
    }

    /**
     * Queues a connection test for the specified server.
     *
     * @param Server $server
     *
     * @return Response
     */
    public function test(Server $server)
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
     * @param Server $server
     *
     * @return Response
     */
    public function destroy(Server $server)
    {
        $server->forceDelete();

        return [
            'success' => true,
        ];
    }
}
