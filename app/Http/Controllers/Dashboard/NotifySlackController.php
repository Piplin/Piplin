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
use Fixhub\Http\Requests\StoreNotifySlackRequest;
use Fixhub\Models\NotifySlack;

/**
 * Controller for managing notifications.
 */
class NotifySlackController extends Controller
{
    /**
     * Store a newly created notification in storage.
     *
     * @param  StoreNotifySlackRequest $request
     * @return Response
     */
    public function store(StoreNotifySlackRequest $request)
    {
        return NotifySlack::create($request->only(
            'name',
            'channel',
            'webhook',
            'project_id',
            'icon',
            'failure_only'
        ));
    }

    /**
     * Update the specified notification in storage.
     *
     * @param  int                     $notification_id
     * @param  StoreNotifySlackRequest $request
     * @return Response
     */
    public function update($notification_id, StoreNotifySlackRequest $request)
    {
        $notification = NotifySlack::findOrFail($notification_id);

        $notification->update($request->only(
            'name',
            'channel',
            'webhook',
            'icon',
            'failure_only'
        ));

        return $notification;
    }

    /**
     * Remove the specified notification from storage.
     *
     * @param  int      $notification_id
     * @return Response
     */
    public function destroy($notification_id)
    {
        $notification = NotifySlack::findOrFail($notification_id);

        $notification->delete();

        return [
            'success' => true,
        ];
    }
}
