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

        Route::get('dashboard', [
            'as'   => 'dashboard.index',
            'uses' => 'ProjectController@index',
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

        Route::get('projects/{id}/environments/{environment_id}/{tab?}', [
            'as'   => 'environments.show',
            'uses' => 'EnvironmentController@show',
        ]);


        Route::post('environments/reorder', [
            'as'    => 'environments.reorder',
            'uses'  => 'EnvironmentController@reorder',
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
        Route::get('projects/{id}/{tab?}', [
            'as'   => 'projects',
            'uses' => 'ProjectController@show',
        ]);

        Route::get('projects/{id}/apply', [
            'as'   => 'projects.apply',
            'uses' => 'ProjectController@apply',
        ]);

        Route::get('deployment/{id}', [
            'as'   => 'deployments',
            'uses' => 'DeploymentController@show',
        ]);

        Route::post('deployment/{id}', [
            'as'   => 'deployments.create',
            'uses' => 'DeploymentController@create',
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

        Route::group(['middleware' => 'admin',
            ], function () use ($actions) {
                Route::post('members/{project_id}', [
                    'uses' => 'MemberController@store',
                ]);

                Route::delete('members/{project_id}/{id}', [
                    'uses' => 'MemberController@destroy',
                ]);

                Route::resource('servers', 'ServerController', $actions);
                Route::resource('variables', 'VariableController', $actions);
                Route::resource('environments', 'EnvironmentController', $actions);
                Route::resource('hooks', 'HookController', $actions);
                Route::resource('commands', 'CommandController', $actions);
                Route::resource('shared-files', 'SharedFilesController', $actions);
                Route::resource('config-file', 'ConfigFileController', $actions);
            });

        Route::get('admin/templates/{id}/commands/{step}', [
            'as'   => 'admin.templates.commands.step',
            'uses' => 'CommandController@index',
        ]);
    });
