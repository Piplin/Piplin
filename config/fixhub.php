<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    // Fixhub specific config
    'socket_url'         => env('SOCKET_URL', 'http://fixhub.app'),
    'theme'              => env('APP_THEME', 'white'),
    'github_oauth_token' => env('GITHUB_OAUTH_TOKEN', false),
    'cdn'                => env('CDN_URL', null),
    'items_per_page'     => 10,
    'gravatar'           => false,
];
