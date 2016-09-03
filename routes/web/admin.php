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
    'middleware' => ['web', 'auth', 'jwt', 'admin'],
    'namespace'  => 'Admin',
    'prefix'     => 'admin',
], function () {
    Route::resource('templates', 'DeployTemplateController', [
        'only'  => ['index', 'store', 'update', 'destroy', 'show'],
        'names' => [
            'index'   => 'admin.templates.index',
            'store'   => 'admin.templates.store',
            'update'  => 'admin.templates.update',
            'destroy' => 'admin.templates.destroy',
            'show'    => 'admin.templates.show',
        ],
    ]);

    Route::resource('projects', 'ProjectController', [
        'only'  => ['index', 'store', 'update', 'destroy'],
        'names' => [
            'index'   => 'admin.projects.index',
            'store'   => 'admin.projects.store',
            'update'  => 'admin.projects.update',
            'destroy' => 'admin.projects.destroy',
        ],
    ]);

    Route::resource('users', 'UserController', [
        'only' => ['index', 'store', 'update', 'destroy'],
        'names' => [
            'index'   => 'admin.users.index',
            'store'   => 'admin.users.store',
            'update'  => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ],
    ]);

    Route::resource('groups', 'ProjectGroupController', [
        'only' => ['index', 'store', 'update', 'destroy'],
        'names' => [
            'index'   => 'admin.groups.index',
            'store'   => 'admin.groups.store',
            'update'  => 'admin.groups.update',
            'destroy' => 'admin.groups.destroy',
        ],
    ]);

    Route::post('groups/reorder', [
        'as'    => 'admin.groups.reorder',
        'uses'  => 'ProjectGroupController@reorder',
    ]);

    Route::resource('settings', 'SettingController', [
        'only'  => ['index'],
        'names' => [
            'index'  => 'admin.settings.index',
        ],
    ]);

    Route::post('settings', [
        'as'   => 'admin.settings.save',
        'uses' => 'SettingController@postSettings'
    ]);
});
