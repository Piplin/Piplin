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
                           'on_deployment_success', 'on_deployment_failure', ];

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
        'id'                         => 'integer',
        'project_id'                 => 'integer',
        'enabled'                    => 'boolean',
        'config'                     => 'object',
        'on_deployment_success'      => 'boolean',
        'on_deployment_failure'      => 'boolean',
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
