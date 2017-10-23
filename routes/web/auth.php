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
    'namespace'  => 'Auth',
], function () {
    Route::get('login', [
            'middleware' => 'guest',
            'as'         => 'auth.login',
            'uses'       => 'AuthController@getLogin',
        ]);

    Route::post('login', [
            'middleware' => ['guest', 'throttle:1000,10'],
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

    Route::get('password/reset', [
            'as'   => 'auth.reset-password',
            'uses' => 'ForgotPasswordController@showLinkRequestForm',
        ]);

    Route::post('password/reset', [
            'as' => 'password.reset',
            'uses' => 'ResetPasswordController@reset',
        ]);

    Route::post('password/email', [
            'as'   => 'auth.reset-email',
            'uses' => 'ForgotPasswordController@sendResetLinkEmail',
        ]);

    Route::get('password/reset/{token?}', [
            'as'   => 'password.reset',
            'uses' => 'ResetPasswordController@showResetForm',
        ]);

    Route::get('logout', [
            'middleware' => 'auth',
            'as'         => 'auth.logout',
            'uses'       => 'AuthController@logout',
        ]);

    // OAuth 2.0 provider
    Route::get('oauth/{oauth_provider}', [
        'as'   => 'oauth.provider',
        'uses' => 'AuthController@provider',
    ]);
    Route::get('oauth/{oauth_provider}/callback', 'AuthController@callback');
});
