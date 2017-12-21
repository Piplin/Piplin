<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    // Piplin specific config
    'socket_url'         => env('SOCKET_URL', 'http://piplin.app'),
    'theme'              => env('APP_THEME', 'white'),
    'github_oauth_token' => env('GITHUB_OAUTH_TOKEN', false),
    'cdn'                => env('CDN_URL', null),
    'items_per_page'     => 10,
    'gravatar'           => false,
    'toastr'             => true,
    'dashboard'          => env('DASHBOARD', 'deployments'),

    'backup_type'        => env('BACKUP_TYPE', 'local'),
];
