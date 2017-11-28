<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Admin;

use Piplin\Http\Controllers\Controller;

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
        $title = trans('admin.home');
        $envs  = [
            ['name' => 'Piplin',         'value' => APP_VERSION],
            ['name' => 'PHP',            'value' => PHP_VERSION],
            ['name' => 'Laravel',        'value' => app()->version()],
            ['name' => 'CGI',            'value' => php_sapi_name()],
            ['name' => 'Server',         'value' => $_SERVER['SERVER_SOFTWARE']],

            ['name' => 'Database',       'value' => config('database.default')],
            ['name' => 'Cache driver',   'value' => config('cache.default')],
            ['name' => 'Session driver', 'value' => config('session.driver')],
            ['name' => 'Queue driver',   'value' => config('queue.default')],

            ['name' => 'Timezone',       'value' => config('app.timezone')],
            ['name' => 'Locale',         'value' => config('app.locale')],
            ['name' => 'Env',            'value' => config('app.env')],
            ['name' => 'URL',            'value' => config('app.url')],
            ['name' => 'Socket URL',     'value' => config('piplin.socket_url')],
            ['name' => 'Socket port',    'value' => env('SOCKET_PORT', 6001)],
            ['name' => 'Mail driver',    'value' => env('MAIL_DRIVER', 'log')],
            ['name' => 'Debug',          'value' => config('app.debug') ? 'true' : 'false'],
            ['name' => 'Log',            'value' => config('app.log')],
            ['name' => 'Log level',      'value' => config('app.log_level')],
        ];

        $json         = file_get_contents(base_path('composer.json'));
        $dependencies = json_decode($json, true)['require'];

        return view('admin.index', compact('title', 'envs', 'dependencies'));
    }
}
