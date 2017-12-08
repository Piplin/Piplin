<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * Kernel class.
 */
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Fideloper\Proxy\TrustProxies::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Piplin\Http\Middleware\EncryptCookies::class,
            \Piplin\Http\Middleware\VerifyCsrfToken::class,
            \Piplin\Http\Middleware\Localize::class,
        ],
        'api' => [
            'throttle:600,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'admin'       => \Piplin\Http\Middleware\Admin::class,
        'project.acl' => \Piplin\Http\Middleware\ProjectAcl::class,
        'auth'        => \Piplin\Http\Middleware\Authenticate::class,
        'auth.basic'  => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest'       => \Piplin\Http\Middleware\RedirectIfAuthenticated::class,
        'jwt'         => \Piplin\Http\Middleware\RefreshJsonWebToken::class,
        'throttle'    => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'localize'    => \Piplin\Http\Middleware\Localize::class,
    ];
}
