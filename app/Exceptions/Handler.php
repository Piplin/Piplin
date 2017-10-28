<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Whoops\Run as Whoops;

/**
 * Exception handler.
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        MethodNotAllowedHttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    /**
     * Exceptions which should not be handled by whoops.
     *
     * @var array
     */
    protected $skipWhoops = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        MethodNotAllowedHttpException::class,
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($this->isHttpException($exception)) {
            return $this->renderHttpException($exception);
        }
        // Use whoops if it is bound to the container and the exception is safe to pass to whoops
        if ($this->container->bound(Whoops::class) && $this->isSafeToWhoops($exception)) {
            return $this->renderExceptionWithWhoops($exception);
        }
        return parent::render($request, $exception);
    }

    /**
     * Render an exception using Whoops.
     *
     * @param  \Exception                $exception
     * @return \Illuminate\Http\Response
     */
    protected function renderExceptionWithWhoops(Exception $exception)
    {
        /** @var Whoops $whoops */
        $whoops = $this->container->make(Whoops::class);
        $statusCode = 500;
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        }
        $headers = [];
        if (method_exists($exception, 'getHeaders')) {
            $headers = $exception->getHeaders();
        }
        return new Response(
            $whoops->handleException($exception),
            $statusCode,
            $headers
        );
    }

    /**
     * Don't allow the exceptions which laravel handles specially to be converted to Whoops.
     *
     * @param \Exception $exception
     *
     * @return bool
     */
    protected function isSafeToWhoops(Exception $exception)
    {
        return is_null(collect($this->skipWhoops)->first(function ($type) use ($exception) {
            return $exception instanceof $type;
        }));
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Auth\AuthenticationException  $exception
     *
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        return redirect()->guest(route('auth.login'));
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @todo Remove this
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json($exception->errors(), $exception->status);
    }
}
