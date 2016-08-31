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
use Fixhub\Http\Requests\StoreHeartbeatRequest;
use Fixhub\Models\Heartbeat;

/**
 * Controller for managing notifications.
 */
class HeartbeatController extends Controller
{
    /**
     * Handles the callback URL for the heartbeat.
     *
     * @param  string   $hash The webhook hash
     * @return Response
     */
    public function ping($hash)
    {
        $heartbeat = Heartbeat::where('hash', $hash)->firstOrFail();

        $heartbeat->pinged();

        return [
            'success' => true,
        ];
    }

    /**
     * Store a newly created heartbeat in storage.
     *
     * @param  StoreHeartbeatRequest $request
     * @return Response
     */
    public function store(StoreHeartbeatRequest $request)
    {
        return Heartbeat::create($request->only(
            'name',
            'interval',
            'project_id'
        ));
    }

    /**
     * Update the specified heartbeat in storage.
     *
     * @param  int                   $heartbeat_id
     * @param  StoreHeartbeatRequest $request
     * @return Response
     */
    public function update($heartbeat_id, StoreHeartbeatRequest $request)
    {
        $heartbeat = Heartbeat::findOrFail($heartbeat_id);

        $heartbeat->update($request->only(
            'name',
            'interval'
        ));

        return $heartbeat;
    }

    /**
     * Remove the specified heartbeat from storage.
     *
     * @param  int      $heartbeat_id
     * @return Response
     */
    public function destroy($heartbeat_id)
    {
        $heartbeat = Heartbeat::findOrFail($heartbeat_id);

        $heartbeat->delete();

        return [
            'success' => true,
        ];
    }
}
