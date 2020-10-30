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
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * User model.
 *
 * @property int                                                                                                            $id
 * @property string                                                                                                         $name
 * @property string                                                                                                         $email
 * @property string                                                                                                         $password
 * @property string|null                                                                                                    $nickname
 * @property string|null                                                                                                    $remember_token
 * @property int                                                                                                            $level
 * @property string|null                                                                                                    $email_token
 * @property string|null                                                                                                    $avatar
 * @property string|null                                                                                                    $language
 * @property string|null                                                                                                    $skin
 * @property string|null                                                                                                    $dashboard
 * @property string|null                                                                                                    $google2fa_secret
 * @property \Illuminate\Support\Carbon|null                                                                                $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                $updated_at
 * @property \Illuminate\Support\Carbon|null                                                                                $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Project[]                                         $authorizedProjects
 * @property-read int|null                                                                                                  $authorized_projects_count
 * @property-read bool                                                                                                      $has_two_factor_authentication
 * @property-read bool                                                                                                      $is_admin
 * @property-read bool                                                                                                      $is_manager
 * @property-read bool                                                                                                      $is_user
 * @property-read string                                                                                                    $role_name
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null                                                                                                  $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Project[]                                         $personalProjects
 * @property-read int|null                                                                                                  $personal_projects_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static Builder|User query()
 * @method static Builder|User search($search = [])
 * @method static Builder|User whereAvatar($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDashboard($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailToken($value)
 * @method static Builder|User whereGoogle2faSecret($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLanguage($value)
 * @method static Builder|User whereLevel($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User whereNickname($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereSkin($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements HasPresenter, JWTSubject
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
        'level' => 'integer',
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
     * @param  string  $name
     * @param  mixed   $arg
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
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array                                  $search
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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'x' => 'piplin',
        ];
    }
}
