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
    'middleware' => ['auth', 'jwt', 'admin'],
    'namespace'  => 'Admin',
    'prefix'     => 'admin',
], function () {
    Route::resource('/', 'AdminController', [
        'only'  => ['index'],
        'names' => [
            'index' => 'admin',
        ],
    ]);

    Route::resource('templates', 'DeployTemplateController', [
        'only'  => ['index', 'store', 'update', 'destroy'],
        'names' => [
            'index'   => 'admin.templates.index',
            'store'   => 'admin.templates.store',
            'update'  => 'admin.templates.update',
            'destroy' => 'admin.templates.destroy',
        ],
    ]);

    Route::get('templates/{template}/{tab?}', [
        'as'   => 'admin.templates.show',
        'uses' => 'DeployTemplateController@show',
    ]);

    Route::resource('projects', 'ProjectController', [
        'only'  => ['create', 'index', 'store', 'update', 'destroy'],
        'names' => [
            'create'  => 'admin.projects.create',
            'index'   => 'admin.projects.index',
            'store'   => 'admin.projects.store',
            'update'  => 'admin.projects.update',
            'destroy' => 'admin.projects.destroy',
        ],
    ]);
    Route::post('projects/{project}/clone', [
        'as'   => 'admin.projects.clone',
        'uses' => 'ProjectController@clone',
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

    Route::resource('keys', 'KeyController', [
        'only' => ['create', 'index', 'store', 'update', 'destroy'],
        'names' => [
            'create'  => 'admin.keys.create',
            'index'   => 'admin.keys.index',
            'store'   => 'admin.keys.store',
            'update'  => 'admin.keys.update',
            'destroy' => 'admin.keys.destroy',
        ],
    ]);
    Route::post('keys/reorder', [
        'as'    => 'admin.keys.reorder',
        'uses'  => 'KeyController@reorder',
    ]);

    Route::resource('links', 'LinkController', [
        'only' => ['create', 'index', 'store', 'update', 'destroy'],
        'names' => [
            'create'  => 'admin.links.create',
            'index'   => 'admin.links.index',
            'store'   => 'admin.links.store',
            'update'  => 'admin.links.update',
            'destroy' => 'admin.links.destroy',
        ],
    ]);
    Route::post('links/reorder', [
        'as'    => 'admin.links.reorder',
        'uses'  => 'LinkController@reorder',
    ]);

    Route::resource('providers', 'ProviderController', [
        'only' => ['create', 'index', 'store', 'update', 'destroy'],
        'names' => [
            'create'  => 'admin.providers.create',
            'index'   => 'admin.providers.index',
            'store'   => 'admin.providers.store',
            'update'  => 'admin.providers.update',
            'destroy' => 'admin.providers.destroy',
        ],
    ]);
    Route::post('providers/reorder', [
        'as'    => 'admin.providers.reorder',
        'uses'  => 'LinkController@reorder',
    ]);

    Route::resource('tips', 'TipController', [
        'only' => ['create', 'index', 'store', 'update', 'destroy'],
        'names' => [
            'create'  => 'admin.tips.create',
            'index'   => 'admin.tips.index',
            'store'   => 'admin.tips.store',
            'update'  => 'admin.tips.update',
            'destroy' => 'admin.tips.destroy',
        ],
    ]);

    Route::resource('groups', 'ProjectGroupController', [
        'only' => ['index', 'create', 'store', 'update', 'destroy'],
        'names' => [
            'index'   => 'admin.groups.index',
            'create'  => 'admin.groups.create',
            'store'   => 'admin.groups.store',
            'update'  => 'admin.groups.update',
            'destroy' => 'admin.groups.destroy',
        ],
    ]);

    Route::post('groups/reorder', [
        'as'    => 'admin.groups.reorder',
        'uses'  => 'ProjectGroupController@reorder',
    ]);

    Route::resource('cabinets', 'CabinetController', [
        'only' => ['index', 'show', 'store', 'update', 'destroy'],
        'names' => [
            'index'   => 'admin.cabinets.index',
            'show'    => 'admin.cabinets.show',
            'store'   => 'admin.cabinets.store',
            'update'  => 'admin.cabinets.update',
            'destroy' => 'admin.cabinets.destroy',
        ],
    ]);

    Route::post('cabinets/reorder', [
        'as'    => 'admin.cabinets.reorder',
        'uses'  => 'CabinetController@reorder',
    ]);

    Route::resource('revisions', 'RevisionController', [
        'only' => ['index'],
        'names' => [
            'index'   => 'admin.revisions.index',
        ],
    ]);

});
