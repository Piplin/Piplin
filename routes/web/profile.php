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
    'namespace'  => 'Profile',
], function () {

    Route::get('profile/{action?}', [
        'as'   => 'profile.index',
        'uses' => 'ProfileController@index',
    ]);

    Route::post('profile/update', [
        'as'   => 'profile.update',
        'uses' => 'ProfileController@update',
    ]);

    Route::post('profile/settings', [
        'as'   => 'profile.settings',
        'uses' => 'ProfileController@settings',
    ]);

    Route::post('profile/email', [
        'as'   => 'profile.request-change-email',
        'uses' => 'ProfileController@requestEmail',
    ]);

    Route::post('profile/upload', [
        'as'   => 'profile.upload-avatar',
        'uses' => 'ProfileController@upload',
    ]);

    Route::post('profile/avatar', [
        'as'   => 'profile.avatar',
        'uses' => 'ProfileController@avatar',
    ]);

    Route::post('profile/gravatar', [
        'as'   => 'profile.gravatar',
        'uses' => 'ProfileController@gravatar',
    ]);

    Route::post('profile/twofactor', [
        'as'   => 'profile.twofactor',
        'uses' => 'ProfileController@twoFactor',
    ]);

    Route::get('profile/email/{token}', [
        'as'   => 'profile.confirm-change-email',
        'uses' => 'ProfileController@email',
    ]);

    Route::post('profile/update-email', [
        'as'   => 'profile.change-email',
        'uses' => 'ProfileController@changeEmail',
    ]);
});
