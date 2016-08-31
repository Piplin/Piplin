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
use Fixhub\Http\Requests\StoreNotifyEmailRequest;
use Fixhub\Models\NotifyEmail;

/**
 * Controller for managing NotifyEmails.
 */
class NotifyEmailController extends Controller
{
    /**
     * Store a newly created NotifyEmail in storage.
     *
     * @param  StoreNotifyEmailRequest $request
     * @return Response
     */
    public function store(StoreNotifyEmailRequest $request)
    {
        return NotifyEmail::create($request->only(
            'name',
            'email',
            'project_id'
        ));
    }

    /**
     * Update the specified NotifyEmail in storage.
     *
     * @param  int                     $email_id
     * @param  StoreNotifyEmailRequest $request
     * @return Response
     */
    public function update($email_id, StoreNotifyEmailRequest $request)
    {
        $notify_email = NotifyEmail::findOrfail($email_id);

        return $notify_email->update($request->only(
            'name',
            'email'
        ));

        return $notify_email;
    }

    /**
     * Remove the specified NotifyEmail from storage.
     *
     * @param  int      $email_id
     * @return Response
     */
    public function destroy($email_id)
    {
        $notify_email = NotifyEmail::findOrFail($email_id);

        $notify_email->delete();

        return [
            'success' => true,
        ];
    }
}
