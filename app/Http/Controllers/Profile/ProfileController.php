<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Profile;

use Fixhub\Bus\Events\EmailChangeRequested;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreProfileRequest;
use Fixhub\Http\Requests\StoreUserSettingsRequest;
use Fixhub\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use PragmaRX\Google2FA\Contracts\Google2FA as Google2FA;

/**
 * The use profile controller.
 */
class ProfileController extends Controller
{
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
     * View user profile.
     * @return Response
     */
    public function index()
    {
        $user = Auth::user();

        $code = $this->google2fa->generateSecretKey();
        if ($user->has_two_factor_authentication || old('google_code')) {
            $code = old('google_code', $user->google2fa_secret);
        }

        $img = $this->google2fa->getQRCodeGoogleUrl('Fixhub', $user->email, $code);

        return view('profile.index', [
            'google_2fa_url'  => $img,
            'google_2fa_code' => $code,
            'title'           => trans('users.update_profile'),
        ]);
    }

    /**
     * Update user's basic profile.
     *
     * @param  StoreProfileRequest $request
     * @return Response
     */
    public function update(StoreProfileRequest $request)
    {
        Auth::user()->update($request->only(
            'nickname',
            'password'
        ));

        return redirect()->to('/');
    }

    /**
     * Update user's settings.
     *
     * @param  StoreUserSettingsRequest $request
     * @return Response
     */
    public function settings(StoreUserSettingsRequest $request)
    {
        Auth::user()->update($request->only(
            'skin',
            'language'
        ));

        return redirect()->to('/');
    }

    /**
     * Send email to change a new email.
     * @return Response
     */
    public function requestEmail()
    {
        event(new EmailChangeRequested(Auth::user()));

        return 'success';
    }

    /**
     * Show the page to input the new email.
     */
    public function email($token)
    {
        return view('profile.change-email', [
            'token' => $token,
        ]);
    }

    /**
     * Change the user's email.
     * @return Response
     */
    public function changeEmail(Request $request)
    {
        $user = User::where('email_token', $request->get('token'))->first();

        if ($request->get('email')) {
            $user->email       = $request->get('email');
            $user->email_token = '';

            $user->save();
        }

        return redirect()->to('/');
    }

    /**
     * Upload file.
     * @return Response
     */
    public function upload(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|image',
        ]);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file            = $request->file('file');
            $path            = '/upload/' . date('Y-m-d');
            $destinationPath = public_path() . $path;
            $filename        = uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move($destinationPath, $filename);

            return [
                'image'   => url($path . '/' . $filename),
                'path'    => $path . '/' . $filename,
                'message' => 'success',
            ];
        } else {
            return 'failed';
        }
    }

    /**
     * Reset the user's avatar to gravatar.
     * @return Response
     */
    public function gravatar()
    {
        $user         = Auth::user();
        $user->avatar = null;
        $user->save();

        return [
            'image'   => $user->avatar_url,
            'success' => true,
        ];
    }

    /**
     * Set and crop the avatar.
     * @return Response
     */
    public function avatar(Request $request)
    {
        $path   = $request->get('path', '/upload/picture.jpg');
        $image  = Image::make(public_path() . $path);
        $rotate = $request->get('dataRotate');

        if ($rotate) {
            $image->rotate($rotate);
        }

        $width  = $request->get('dataWidth');
        $height = $request->get('dataHeight');
        $left   = $request->get('dataX');
        $top    = $request->get('dataY');

        $image->crop($width, $height, $left, $top);
        $path = '/upload/' . date('Y-m-d') . '/avatar' . uniqid() . '.jpg';

        $image->save(public_path() . $path);

        $user         = Auth::user();
        $user->avatar = $path;
        $user->save();

        return [
            'image'   => url($path),
            'success' => true,
        ];
    }

    /**
     * Activates two factor authentication.
     * @param  Request  $request
     * @return Response
     */
    public function twoFactor(Request $request)
    {
        $secret = null;
        if ($request->has('two_factor')) {
            $secret = $request->get('google_code');

            if (!$this->google2fa->verifyKey($secret, $request->get('2fa_code'))) {
                $secret = null;

                return redirect()->back()
                                 ->withInput($request->only('google_code', 'two_factor'))
                                 ->withError(trans('auth.invalid_code'));
            }
        }

        $user                   = Auth::user();
        $user->google2fa_secret = $secret;
        $user->save();

        return redirect()->to('/');
    }
}
