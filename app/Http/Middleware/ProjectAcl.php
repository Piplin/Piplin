<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Project Acl middleware.
 */
class ProjectAcl extends AbstractMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              $ability
     * @return mixed
     */
    public function handle($request, Closure $next, $ability = null)
    {
        $project = $request->route('project');

        if (!$project) {
            foreach (['environment', 'command', 'variable', 'config_file', 'shared_file'] as $key) {
                $module = $request->route($key);

                if ($module && $project = $module->targetable) {
                    break;
                }
            }
        }

        if (!$project || !$project->can($ability, $request->user() ?: null)) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }
}
