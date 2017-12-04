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

    /*
    |--------------------------------------------------------------------------
    | Define the languages you want exported messages for
    |--------------------------------------------------------------------------
    */

    'locales' => ['en','zh-CN'],

    /*
    |--------------------------------------------------------------------------
    | Define the messages to export
    |--------------------------------------------------------------------------
    |
    | An array containing the keys of the messages you wish to make accessible
    | for the Javascript code.
    | Remember that the number of messages sent to the browser influences the
    | time the website needs to load. So you are encouraged to limit these
    | messages to the minimum you really need.
    |
    | Supports nesting:
    |   [ 'mynamespace' => ['test1', 'test2'] ]
    | for instance will be internally resolved to:
    |   ['mynamespace.test1', 'mynamespace.test2']
    |
    */

    'messages' => [
        'app'           => ['yes', 'no', 'never', 'copied'],
        'dashboard'     => ['pending', 'pending_empty', 'running', 'running_empty', 'approving', 'approving_empty', 'deployment_number'],
        'tasks'         => ['create_success', 'completed', 'completed_with_errors', 'pending', 'running', 'cancelled', 'failed', 'draft', 'submit_success', 'build', 'build_title', 'deploy', 'deploy_title'],
        'variables'     => ['create', 'create_success', 'edit', 'edit_success', 'delete_success'],
        'environments'  => ['create', 'create_success', 'edit', 'edit_success', 'delete_success', 'link_auto', 'link_manual', 'link_success'],
        'projects'      => ['create', 'create_success', 'edit', 'edit_success', 'delete_success', 'finished', 'pending', 'running', 'failed', 'not_deployed'],
        'commands'      => ['create', 'create_success', 'edit', 'edit_success', 'delete_success'],
        'groups'        => ['create', 'create_success', 'edit', 'edit_success', 'delete_success'],
        'keys'          => ['create', 'create_success', 'edit', 'edit_success', 'delete_success'],
        'patterns'      => ['create', 'create_success', 'edit', 'edit_success', 'delete_success'],
        'users'         => ['create', 'create_success', 'edit', 'edit_success', 'delete_success'],
        'releases'      => ['delete_success'],
        'sharedFiles'   => ['create', 'create_success', 'edit', 'edit_success', 'delete_success'],
        'configFiles'   => ['create', 'create_success', 'edit', 'edit_success', 'delete_success'],
        'hooks'         => ['create', 'create_success', 'edit', 'edit_success', 'delete_success', 'custom', 'slack', 'dingtalk', 'mail', 'create_slack', 'create_dingtalk', 'create_mail', 'create_custom', 'edit_slack', 'edit_dingtalk', 'edit_mail', 'edit_custom'],
        'providers'     => ['create', 'create_success', 'edit', 'edit_success', 'delete_success'],
        'servers'       => ['create', 'create_success', 'edit', 'edit_success', 'delete_success', 'successful', 'testing', 'failed', 'untested'],
        'members'       => ['create', 'create_success', 'edit', 'edit_success', 'delete_success', 'search'],
        'cabinets'      => ['create', 'create_success', 'edit', 'edit_success', 'delete_success', 'link', 'link_success', 'search'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Set the keys of config properties you want to use in javascript.
    | Caution: Do not expose any configuration values that should be kept privately!
    |--------------------------------------------------------------------------
    */
    'config' => [
        'app.debug',  'piplin.toastr',
    ],

    /*
    |--------------------------------------------------------------------------
    | Disables the config cache if set to true, so you don't have to
    | run `php artisan js-localization:refresh` each time you change configuration files.
    | Attention: Should not be used in production mode due to decreased performance.
    |--------------------------------------------------------------------------
    */
    'disable_config_cache' => env('APP_DEBUG', false),

];
