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

use Auth;
use Closure;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;

/**
 * Localization middleware.
 */
class Localize
{
    /**
     * Array of languages Fixhub can use.
     *
     * @var array
     */
    protected $langs;

    /**
     * The config repository instance.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Constructs a new localize middleware instance.
     *
     * @param \Illuminate\Config\Repository $config
     *
     * @return void
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->langs  = $config->get('langs');
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userLanguage = Auth::check() && Auth::user()->language ? Auth::user()->language : null;

        if (!$userLanguage) {
            $supportedLanguages = $request->getLanguages();
            $userLanguage       = $this->config->get('app.locale');

            foreach ($supportedLanguages as $language) {
                $language = str_replace('_', '-', $language);

                if (isset($this->langs[$language])) {
                    $userLanguage = $language;
                    break;
                }
            }
        }

        app('translator')->setLocale($userLanguage);

        return $next($request);
    }
}
