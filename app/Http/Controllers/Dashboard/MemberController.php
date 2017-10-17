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
use Illuminate\Http\Request;
use Fixhub\Models\Variable;

/**
 * Project members management controller.
 */
class MemberController extends Controller
{
    /**
     * Update the specified variable in storage.
     *
     * @param  int                  $user_id
     * @param  Request $request
     * @return Response
     */
    public function update($user_id, Request $request)
    {
        print_r($request->all());
    }
}
