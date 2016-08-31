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

use Fixhub\Bus\Jobs\SlackNotify;
use Fixhub\Models\Traits\BroadcastChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Slack notification model.
 */
class NotifySlack extends Model
{
    use SoftDeletes, DispatchesJobs, BroadcastChanges;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'channel', 'webhook', 'project_id', 'icon', 'failure_only'];

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
        'id'           => 'integer',
        'project_id'   => 'integer',
        'failure_only' => 'boolean',
    ];

    /**
     * Belongs to relationship.
     *
     * @return Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Override the boot method to bind model event listeners.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        // When the notification has been saved queue a test
        static::saved(function (NotifySlack $model) {
            $model->dispatch(new SlackNotify($model, $model->testPayload()));
        });
    }

    /**
     * Generates a test payload for Slack.
     *
     * @return array
     */
    public function testPayload()
    {
        return [
            'text' => trans('notifySlacks.test_message'),
        ];
    }
}
