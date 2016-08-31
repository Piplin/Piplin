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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Exception handler.
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception                $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($this->isHttpException($exception)) {
            return $this->renderHttpException($exception);
        }

        // Only show whoops pages if debugging is enabled and it is installed, i.e. on dev
        // and for exceptions which should actually show an exception page
        if (config('app.debug') && class_exists('\Whoops\Run', true) && $this->isSafeToWhoops($exception)) {
            return $this->renderExceptionWithWhoops($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Render an exception using Whoops.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception                $exception
     * @return \Illuminate\Http\Response
     */
    protected function renderExceptionWithWhoops($request, Exception $exception)
    {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());

        if ($request->ajax()) {
            $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
        }

        return new Response(
            $whoops->handleException($exception),
            $exception->getStatusCode(),
            $exception->getHeaders()
        );
    }

    /**
     * Don't allow the exceptions which laravel handles specially to be converted to Whoops
     * This is horrible though, see if we can find a better way to do it.
     * GrahamCampbell/Laravel-Exceptions unfortunately doesn't return JSON for whoops pages which are from AJAX.
     *
     * @param  \Exception $exception
     * @return bool
     */
    protected function isSafeToWhoops(Exception $exception)
    {
        if ($exception instanceof HttpResponseException) {
            return false;
        } elseif ($exception instanceof ModelNotFoundException) {
            return false;
        } elseif ($exception instanceof AuthorizationException) {
            return false;
        } elseif ($exception instanceof ValidationException && $exception->getResponse()) {
            return false;
        }

        return true;
    }
}
