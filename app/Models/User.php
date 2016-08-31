<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Models;

use Fixhub\Models\Traits\BroadcastChanges;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Creativeorange\Gravatar\Facades\Gravatar;

/**
 * User model.
 */
class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, Authorizable, SoftDeletes, BroadcastChanges;

    /**
     * The admin level of user.
     *
     * @var int
     */
    const LEVEL_ADMIN = 1;

    /**
     * The general level of user.
     *
     * @var int
     */
    const LEVEL_USER = 2;

    /**
     * The operator level of user.
     *
     * @var int
     */
    const LEVEL_OPERATOR = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'nickname', 'level', 'avatar', 'skin', 'language'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['deleted_at', 'updated_at', 'password', 'remember_token', 'google2fa_secret'];

    /**
     * Additional attributes to include in the JSON representation.
     *
     * @var array
     */
    protected $appends = ['has_two_factor_authentication', 'role_name'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Generate a change email token.
     *
     * @return string
     */
    public function requestEmailToken()
    {
        $this->email_token = str_random(40);
        $this->save();

        return $this->email_token;
    }

    /**
     * Returns the user avatar url.
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return url($this->avatar);
        }

        return Gravatar::get($this->email);
    }

    /**
     * Returns whether a user is at admin level.
     *
     * @return bool
     */
    public function getIsAdminAttribute()
    {
        return $this->level == self::LEVEL_ADMIN;
    }

    /**
     * Returns whether a user is at operator level.
     *
     * @return bool
     */
    public function getIsOperatorAttribute()
    {
        return $this->level == self::LEVEL_OPERATOR;
    }

    /**
     * Determines whether the user has Google 2FA enabled.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHasTwoFactorAuthenticationAttribute()
    {
        return !empty($this->google2fa_secret);
    }

    /**
     * Get the user role.
     *
     * @return string
     */
    public function getRoleNameAttribute()
    {
        if ($this->level == User::LEVEL_ADMIN) {
            return trans('users.level.admin');
        } elseif ($this->level == User::LEVEL_OPERATOR) {
            return trans('users.level.operator');
        } elseif ($this->level == User::LEVEL_USER) {
            return trans('users.level.user');
        }

        return 'Unknown';
    }
}
