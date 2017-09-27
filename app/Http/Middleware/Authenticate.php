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

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentication middleware.
 */
class Authenticate
{
    /**
     * @var Redirector
     */
    private $redirector;
    /**
     * @var ResponseFactory
     */
    private $response;
    /**
     * @var AuthFactory
     */
    private $auth;
    /**
     * @param Redirector      $redirector
     * @param ResponseFactory $response
     * @param AuthFactory     $auth
     */
    public function __construct(Redirector $redirector, ResponseFactory $response, AuthFactory $auth)
    {
        $this->redirector = $redirector;
        $this->response   = $response;
        $this->auth       = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     * @param string|null              $guard
     *
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            if ($request->ajax()) {
                return $this->response->make('Unauthorized.', Response::HTTP_UNAUTHORIZED);
            }
            return $this->redirector->guest('auth/login');
        }
        return $next($request);
    }
}
