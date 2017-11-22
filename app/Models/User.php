<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Models;

use Creativeorange\Gravatar\Facades\Gravatar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use McCool\LaravelAutoPresenter\HasPresenter;
use Piplin\Bus\Notifications\User\ResetPasswordNotification;
use Piplin\Models\Traits\BroadcastChanges;
use Piplin\Presenters\UserPresenter;

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
     * The management level of user.
     *
     * @var int
     */
    const LEVEL_MANAGER = 2;

    /**
     * The general level of user.
     *
     * @var int
     */
    const LEVEL_COLLABORATOR = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'nickname', 'level', 'avatar', 'skin', 'language', 'dashboard'];

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
     * @return Project
     */
    public function authorizedProjects()
    {
        return $this->belongsToMany(Project::class)
                    ->orderBy('id', 'ASC');
    }

    /**
     * Has many relationship.
     *
     * @return Project
     */
    public function personalProjects()
    {
        return $this->morphMany(Project::class, 'targetable');
    }

    /**
     * Checks ability for user.
     *
     * @param string $name
     * @param mixed  $arg
     *
     * @return bool
     */
    public function can($name, $arg = null)
    {
        if ($name === 'projects.create') {
            return $this->is_admin || $this->is_manager;
        }

        return false;
    }

    /**
     * Returns whether a user is at user level.
     *
     * @return bool
     */
    public function getIsUserAttribute()
    {
        return $this->level === self::LEVEL_COLLABORATOR;
    }

    /**
     * Returns whether a user is at admin level.
     *
     * @return bool
     */
    public function getIsAdminAttribute()
    {
        return $this->level === self::LEVEL_ADMIN;
    }

    /**
     * Returns whether a user is at management level.
     *
     * @return bool
     */
    public function getIsManagerAttribute()
    {
        return $this->level === self::LEVEL_MANAGER;
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
     * @param  string $token
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
        if ($this->level === self::LEVEL_ADMIN) {
            return trans('users.level.admin');
        } elseif ($this->level === self::LEVEL_MANAGER) {
            return trans('users.level.manager');
        } elseif ($this->level === self::LEVEL_COLLABORATOR) {
            return trans('users.level.collaborator');
        }

        return 'Unknown';
    }

    /**
     * Adds a search scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $search
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
