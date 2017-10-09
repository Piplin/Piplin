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
use Fixhub\Models\Revision;

/**
 * Revision management controller.
 */
class RevisionController extends Controller
{
    /**
     * Revision listing.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $revisions = Revision::orderBy('id', 'desc')->paginate(config('fixhub.items_per_page', 10));

        return view('admin.revisions.index', [
            'title'     => trans('revisions.manage'),
            'revisions' => $revisions,
        ]);
    }
}
