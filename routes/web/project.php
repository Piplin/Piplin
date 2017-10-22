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
        'middleware' => ['auth', 'jwt', 'project.acl:view'],
        'namespace'  => 'Dashboard',
    ], function () {
        // Project
        Route::get('projects/{project}/{tab?}', [
            'as'   => 'projects',
            'uses' => 'ProjectController@show',
        ]);

        Route::get('projects/{project}/apply', [
            'as'   => 'projects.apply',
            'uses' => 'ProjectController@apply',
        ]);
    });
