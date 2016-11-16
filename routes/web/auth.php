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
    'middleware' => ['web'],
    'namespace'  => 'Auth',
    'as'         => 'auth.',
    'prefix'     => 'auth',
], function () {
    Route::get('login', [
            'middleware' => 'guest',
            'as'         => 'login',
            'uses'       => 'AuthController@getLogin',
        ]);

    Route::post('login', [
            'middleware' => ['guest', 'throttle:10,10'],
            'as'         => 'login-verify',
            'uses'       => 'AuthController@postLogin',
        ]);

    Route::get('login/2fa', [
            'as'   => 'twofactor',
            'uses' => 'AuthController@getTwoFactorAuthentication',
        ]);

    Route::post('login/2fa', [
            'middleware' => 'throttle:10,10',
            'as'         => 'twofactor-verify',
            'uses'       => 'AuthController@postTwoFactorAuthentication',
        ]);

    Route::get('password/reset/{token?}', [
            'as'   => 'reset-password-confirm',
            'uses' => 'PasswordController@showResetForm',
        ]);

    Route::post('password/email', [
            'as'   => 'request-password-reset',
            'uses' => 'PasswordController@sendResetLinkEmail',
        ]);

    Route::post('password/reset', [
            'as'   => 'reset-password',
            'uses' => 'PasswordController@reset',
        ]);

    Route::get('logout', [
            'middleware' => 'auth',
            'as'         => 'logout',
            'uses'       => 'AuthController@logout',
        ]);

    Route::get('{provider}', 'AuthController@provider');
    Route::get('{provider}/callback', 'AuthController@callback');
});
