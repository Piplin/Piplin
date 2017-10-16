<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Api;

use Fixhub\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * The Auto-complete controller.
 */
class AutocompleteController extends Controller
{
    /**
     * Search users by key word.
     *
     * @param  Request  $request
     * @return Response
     */
    public function users(Request $request)
    {
        $q = $request->get('q');

        $response = [
            ['id' => 1, 'username' => '张三'.$q],
            ['id' => 2, 'username' => '李四'.$q],
            ['id' => 3, 'username' => '王五'.$q],
        ];

        return Response::json($response);
    }
}
