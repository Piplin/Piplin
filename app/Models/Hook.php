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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Piplin\Models\Traits\BroadcastChanges;

/**
 * Notification hook.
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property bool $enabled
 * @property object $config
 * @property bool $on_task_success
 * @property bool $on_task_failure
 * @property int $project_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Piplin\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|Hook enabled()
 * @method static \Illuminate\Database\Eloquent\Builder|Hook forEvent($event)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hook newQuery()
 * @method static \Illuminate\Database\Query\Builder|Hook onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Hook query()
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereOnTaskFailure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereOnTaskSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hook whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Hook withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Hook withoutTrashed()
 * @mixin \Eloquent
 */
class Hook extends Model
{
    use SoftDeletes, BroadcastChanges, Notifiable;

    const EMAIL    = 'mail';
    const SLACK    = 'slack';
    const DINGTALK = 'dingtalk';
    const WEBHOOK  = 'custom';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'project_id', 'type', 'enabled', 'config',
                           'on_task_success', 'on_task_failure', ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'              => 'integer',
        'project_id'      => 'integer',
        'enabled'         => 'boolean',
        'config'          => 'object',
        'on_task_success' => 'boolean',
        'on_task_failure' => 'boolean',
    ];

    /**
     * Belongs to relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Returns the email address to send the notification to.
     *
     * @return string|null
     */
    public function routeNotificationForMail()
    {
        if ($this->type === self::EMAIL) {
            return $this->config->email;
        }
    }

    /**
     * Returns the URL for the slack webhook.
     *
     * @return string|null
     */
    public function routeNotificationForSlack()
    {
        if ($this->type === self::SLACK) {
            return $this->config->webhook;
        }
    }

    /**
     * Returns the URL for the dingtalk webhook.
     *
     * @return string|null
     */
    public function routeNotificationForDingtalk()
    {
        if ($this->type === self::DINGTALK) {
            return $this->config->webhook;
        }
    }

    /**
     * Returns the URL for the custom webhook.
     *
     * @return string|null
     */
    public function routeNotificationForWebhook()
    {
        if ($this->type === self::WEBHOOK) {
            return $this->config->url;
        }
    }

    /**
     * Scope a query to only include notifications for a specific event.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string                                $event
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEvent($query, $event)
    {
        return $query->where('on_' . $event, '=', true);
    }

    /**
     * Find all hooks which are enabled.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', '=', true);
    }
}
