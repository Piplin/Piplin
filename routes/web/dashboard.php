<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group([
        'middleware' => ['web', 'auth', 'jwt'],
        'namespace'  => 'Dashboard',
    ], function () {
        Route::get('/', [
            'as'   => 'dashboard',
            'uses' => 'DashboardController@index',
        ]);

        Route::get('timeline', [
            'as'   => 'dashboard.timeline',
            'uses' => 'DashboardController@timeline',
        ]);

        Route::post('commands/reorder', [
            'as'   => 'commands.reorder',
            'uses' => 'CommandController@reorder',
        ]);

        Route::get('projects/{id}/commands/{step}', [
            'as'   => 'commands.step',
            'uses' => 'CommandController@index',
        ]);

        Route::post('servers/reorder', [
            'as'    => 'servers.reorder',
            'uses'  => 'ServerController@reorder',
        ]);

        Route::get('servers/{id}/test', [
            'as'    => 'servers.test',
            'uses'  => 'ServerController@test',
        ]);

        // Webhook
        Route::get('webhook/{id}/refresh', [
            'middleware' => 'admin',
            'as'         => 'webhook.refresh',
            'uses'       => 'WebhookController@refresh',
        ]);

        // Project
        Route::get('projects/{id}', [
            'as'   => 'projects',
            'uses' => 'ProjectController@show',
        ]);

        Route::get('projects/{id}/apply', [
            'as'   => 'projects.apply',
            'uses' => 'ProjectController@apply',
        ]);

        Route::post('projects/{id}/deploy', [
            'as'   => 'projects.deploy',
            'uses' => 'ProjectController@deploy',
        ]);

        // Deployment
        Route::post('deployment/{id}/rollback', [
            'as'   => 'deployments.rollback',
            'uses' => 'DeploymentController@rollback',
        ]);

        Route::get('deployment/{id}/abort', [
            'as'   => 'deployments.abort',
            'uses' => 'DeploymentController@abort',
        ]);

        Route::get('deployment/{id}/approve', [
            'as'    => 'deployments.approve',
            'uses'  => 'DeploymentController@approve',
        ]);

        Route::get('deployment/{id}/deploy', [
            'as'    => 'deployments.deploy',
            'uses'  => 'DeploymentController@deploy',
        ]);

        Route::get('deployment/{id}', [
            'as'   => 'deployments',
            'uses' => 'DeploymentController@show',
        ]);

        Route::get('log/{log}', [
            'as'   => 'server_log.show',
            'uses' => 'ServerLogController@show',
        ]);

        Route::get('repository/{id}/refresh', [
            'as'     => 'repository.refresh',
            'uses'   => 'RepositoryController@refresh',
            'middle' => 'api',
        ]);

        $actions = [
            'only' => ['store', 'update', 'destroy'],
        ];

        Route::resource('servers', 'ServerController', $actions);
        Route::resource('variables', 'VariableController', $actions);
        Route::resource('commands', 'CommandController', $actions);
        Route::resource('notify-slack', 'NotifySlackController', $actions);
        Route::resource('shared-files', 'SharedFilesController', $actions);
        Route::resource('config-file', 'ConfigFileController', $actions);
        Route::resource('notify-email', 'NotifyEmailController', $actions);

        Route::get('admin/templates/{id}/commands/{step}', [
            'as'   => 'admin.templates.commands.step',
            'uses' => 'CommandController@index',
        ]);
    });
