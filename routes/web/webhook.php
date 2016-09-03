<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::get('cctray.xml', [
    'as'   => 'cctray',
    'uses' => 'Dashboard\DashboardController@cctray',
]);

Route::post('deploy/{hash}', [
    'as'         => 'webhook.deploy',
    'middleware' => 'api',
    'uses'       => 'WebhookController@webhook',
]);

Route::get('heartbeat/{hash}', [
    'as'   => 'heartbeats',
    'uses' => 'Dashboard\HeartbeatController@ping',
]);
