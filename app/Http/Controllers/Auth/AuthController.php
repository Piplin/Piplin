<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Auth;

use Fixhub\Http\Controllers\Controller;
use Fixhub\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Contracts\Google2FA as Google2FA;

/**
 * Authentication controller.
 */
class AuthController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect to once the login has been successful.
     *
     * @var string
     */
    protected $redirectTo = '/';

    private $google2fa;

    /**
     * Class constructor.
     *
     * @param  Google2FA $google2fa
     * @return void
     */
    public function __construct(Google2FA $google2fa)
    {
        $this->google2fa  = $google2fa;
    }

    /**
     * Show the application login form.
     *
     * @return Response
     */
    public function getLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  Request  $request
     * @return Response
     */
    public function postLogin(Request $request)
    {
        $credentials = $request->only(['login', 'password']);

        // Login with username or email.
        $loginKey = Str::contains($credentials['login'], '@') ? 'email' : 'name';
        $credentials[$loginKey] = array_pull($credentials, 'login');

        if (Auth::validate($credentials)) {
            Auth::once($credentials);

            if (Auth::user()->has_two_factor_authentication) {
                Session::put('2fa_user_id', Auth::user()->id);
                Session::put('2fa_remember', $request->has('remember'));

                $this->clearLoginAttempts($request);

                return redirect()->route('auth.twofactor');
            }

            Auth::attempt($credentials, $request->has('remember'));

            return Redirect::intended('/');
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Shows the 2FA form.
     *
     * @return Response
     */
    public function getTwoFactorAuthentication()
    {
        return view('auth.twofactor');
    }

    /**
     * Validates the 2FA code.
     *
     * @param  Request  $request
     * @return Response
     */
    public function postTwoFactorAuthentication(Request $request)
    {
        $user_id  = Session::pull('2fa_user_id');
        $remember = Session::pull('2fa_login_remember');

        if ($user_id) {
            Auth::loginUsingId($user_id, $remember);

            if ($this->google2fa->verifyKey(Auth::user()->google2fa_secret, $request->get('2fa_code'))) {
                return $this->handleUserWasAuthenticated($request, true);
            }

            Auth::logout();

            return redirect()->route('auth.login')
                             ->withError(trans('auth.invalid_code'));
        }

        return redirect()->route('auth.login')
                         ->withError(trans('auth.invalid_code'));
    }
}
