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
use Fixhub\Bus\Events\JsonWebTokenExpired;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTAuth;

/**
 * Middleware to ensure the JSON web token is still valid.
 */
class RefreshJsonWebToken
{
    /**
     * @var JWTAuth
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param JWTAuth $auth
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $autheticated_user = Auth::guard($guard)->user();

        $has_valid_token = false;

        // Is the user has used "remember me" the token may not be in their session when they return
        if ($request->session()->has('jwt')) {
            $token = $request->session()->get('jwt');

            try {
                $token_user = $this->auth->authenticate($token);

                if ($token_user->id !== $autheticated_user->id) {
                    throw new JWTException('Token does not belong to the authenticated user');
                }

                $has_valid_token = true;
            } catch (TokenExpiredException $e) {
                $has_valid_token = false;
            } catch (JWTException $e) {
                if ($request->ajax()) {
                    return response('Unauthorized.', 401);
                } else {
                    return redirect()->guest('auth/login');
                }
            }
        }

        // If there is no valid token, generate one
        if (!$has_valid_token) {
            event(new JsonWebTokenExpired($autheticated_user));
        }

        return $next($request);
    }
}
