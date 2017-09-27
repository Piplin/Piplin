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
use Fixhub\Presenters\UserPresenter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use McCool\LaravelAutoPresenter\HasPresenter;
use Illuminate\Support\Facades\Hash;
use Creativeorange\Gravatar\Facades\Gravatar;

/**
 * User model.
 */
class User extends Authenticatable implements HasPresenter
{
    use SoftDeletes, BroadcastChanges, Notifiable;

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
    * Has any password being inserted by default.
    *
    * @param string $password
    *
    * @return \Fixhub\Models\User
    */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);

        return $this;
    }

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
     * Returns whether a user is at user level.
     *
     * @return bool
     */
    public function getIsUserAttribute()
    {
        return $this->level == self::LEVEL_USER;
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

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return UserPresenter::class;
    }
}
