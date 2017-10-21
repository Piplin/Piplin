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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use McCool\LaravelAutoPresenter\HasPresenter;
use Illuminate\Support\Facades\Hash;
use Creativeorange\Gravatar\Facades\Gravatar;
use Fixhub\Bus\Notifications\User\ResetPasswordNotification;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * User model.
 */
class User extends Authenticatable implements HasPresenter
{
    use SoftDeletes, BroadcastChanges, Notifiable, RevisionableTrait;

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
     * Revision creations enabled.
     *
     * @var boolean
     */
    protected $revisionCreationsEnabled = true;

    /**
     * Revision ignore attributes.
     *
     * @var array
     */
    protected $dontKeepRevisionOf = ['password', 'remember_token', 'email_token'];


    /**
     * The searchable fields.
     *
     * @var string[]
     */
    protected $searchable = ['id', 'name', 'email', 'nickname'];

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
     * Belongs to many relationship.
     *
     * @return Server
     */
    
    public function projects()
    {
        return $this->belongsToMany(Project::class)
                    ->orderBy('id', 'ASC');
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
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
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
     * Adds a search scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $search
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch(Builder $query, array $search = [])
    {
        if (empty($search)) {
            return $query;
        }
        if (!array_intersect(array_keys($search), $this->searchable)) {
            return $query;
        }
        return $query->where($search);
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
