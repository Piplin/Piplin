<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Middleware;

use Illuminate\Http\Request;

abstract class AbstractMiddleware
{
    /**
     * @param Request $request
     * @return mixed
     */
    protected function unauthorized(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        }

        abort(401, 'Unauthorized');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function login(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        }
        return redirect()->guest(route('auth.login'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirect(Request $request)
    {
        return redirect()->route(
            $request->route()->getName(),
            array_merge($request->route()->parameters(), $request->query()),
            301
        );
    }
}
