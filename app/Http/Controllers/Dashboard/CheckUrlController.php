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
use Fixhub\Http\Requests\StoreCheckUrlRequest;
use Fixhub\Models\CheckUrl;

/**
 * Controller for managing URLs.
 */
class CheckUrlController extends Controller
{
    /**
     * Store a newly created URL in storage.
     *
     * @param  StoreCheckUrlRequest $request
     * @return Response
     */
    public function store(StoreCheckUrlRequest $request)
    {
        return CheckUrl::create($request->only(
            'title',
            'url',
            'is_report',
            'period',
            'project_id'
        ));
    }

    /**
     * Update the specified URL in storage.
     *
     * @param  int                  $url_id
     * @param  StoreCheckUrlRequest $request
     * @return Response
     */
    public function update($url_id, StoreCheckUrlRequest $request)
    {
        $check_url = CheckUrl::findOrFail($url_id);

        $check_url->update($request->only(
            'title',
            'url',
            'is_report',
            'period'
        ));

        return $check_url;
    }

    /**
     * Remove the specified check url from storage.
     *
     * @param  int      $url_id
     * @return Response
     */
    public function destroy($url_id)
    {
        $check_url = CheckUrl::findOrFail($url_id);

        $check_url->delete();

        return [
            'success' => true,
        ];
    }
}
