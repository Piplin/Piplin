<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Admin;

use Fixhub\Http\Controllers\Controller;

/**
 * Controller for admin.
 */
class AdminController extends Controller
{
    /**
     * Shows admin.
     *
     * @return Response
     */
    public function index()
    {
        return view('admin.index', [
            'title' => 'Admin',
        ]);
    }
}
