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
        'middleware' => ['auth', 'jwt'],
        'namespace'  => 'Dashboard',
    ], function () {
        // Project
        Route::get('projects/{project}/{tab?}', [
            'as'   => 'projects',
            'uses' => 'ProjectController@show',
        ])->middleware('project.acl:view');

        Route::post('commands/reorder', [
            'as'   => 'commands.reorder',
            'uses' => 'CommandController@reorder',
        ]);

        Route::get('projects/{project}/commands/{step}', [
            'as'   => 'commands.step',
            'uses' => 'CommandController@index',
        ]);

        Route::get('deployment/{deployment}', [
            'as'   => 'deployments',
            'uses' => 'DeploymentController@show',
        ]);

        Route::post('deployment/{project}', [
            'as'   => 'deployments.create',
            'uses' => 'DeploymentController@create',
        ])->middleware('project.acl:deploy');

        // Deployment
        Route::post('deployment/{deployment}/rollback', [
            'as'   => 'deployments.rollback',
            'uses' => 'DeploymentController@rollback',
        ])->middleware('project.acl:deploy');

        Route::get('deployment/{deployment}/abort', [
            'as'   => 'deployments.abort',
            'uses' => 'DeploymentController@abort',
        ])->middleware('project.acl:deploy');

        Route::get('log/{log}', [
            'as'   => 'server_log.show',
            'uses' => 'ServerLogController@show',
        ]);


        Route::group(['middleware' => 'project.acl:manage',
            ], function () {
                $actions = [
                    'only' => ['store', 'update', 'destroy'],
                ];

                // Webhook
                Route::get('webhook/{project}/refresh', [
                    'middleware' => 'admin',
                    'as'         => 'webhook.refresh',
                    'uses'       => 'WebhookController@refresh',
                ]);

                // Member
                Route::post('members/{project}', [
                    'uses' => 'MemberController@store',
                ]);

                Route::delete('members/{project}/{id}', [
                    'uses' => 'MemberController@destroy',
                ]);

                // Server
                Route::post('servers/{project}', [
                    'uses' => 'ServerController@store',
                ]);
                Route::put('servers/{project}/{server}', [
                    'uses' => 'ServerController@update',
                ]);
                Route::delete('servers/{project}/{server}', [
                    'uses' => 'ServerController@destroy',
                ]);
                Route::post('servers/{project}/reorder', [
                    'uses' => 'ServerController@reorder',
                ]);
                Route::get('servers/{project}/{server}/test', [
                    'as'    => 'servers.test',
                    'uses'  => 'ServerController@test',
                ]);

                // Hook
                Route::post('hooks/{project}', [
                    'uses' => 'HookController@store',
                ]);
                Route::put('hooks/{project}/{hook}', [
                    'uses' => 'HookController@update',
                ]);
                Route::delete('hooks/{project}/{hook}', [
                    'uses' => 'HookController@destroy',
                ]);

                // Refresh
                Route::get('repository/{id}/refresh', [
                    'as'     => 'repository.refresh',
                    'uses'   => 'RepositoryController@refresh',
                    'middle' => 'api',
                ]);

                // Environment
                Route::get('projects/{project}/environments/{environment}/{tab?}', [
                    'as'   => 'environments.show',
                    'uses' => 'EnvironmentController@show',
                ]);
                Route::post('environments/{project}/reorder', [
                    'as'    => 'environments.reorder',
                    'uses'  => 'EnvironmentController@reorder',
                ]);

                Route::resource('variables', 'VariableController', $actions);
                Route::resource('environments', 'EnvironmentController', $actions);
                Route::resource('commands', 'CommandController', $actions);
                Route::resource('shared-files', 'SharedFilesController', $actions);
                Route::resource('config-file', 'ConfigFileController', $actions);
            });
    });
