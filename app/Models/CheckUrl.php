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

use Fixhub\Bus\Jobs\RequestProjectCheckUrl;
use Fixhub\Models\Traits\BroadcastChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Lang;

/**
 * The application's  url store for health check.
 */
class CheckUrl extends Model
{
    use SoftDeletes, BroadcastChanges, DispatchesJobs;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'url', 'project_id', 'period', 'is_report'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'deleted_at', 'pivot'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'         => 'integer',
        'project_id' => 'integer',
        'is_report'  => 'boolean',
        'period'     => 'integer',
    ];

    /**
     * Override the boot method to bind model event listeners.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        // When saving the model, if the URL has changed we need to test it
        static::saved(function (CheckUrl $model) {
            if (is_null($model->last_status)) {
                $model->dispatch(new RequestProjectCheckUrl([$model]));
            }
        });
    }

    /**
     * Define a mutator to set the status to untested if the URL changes.
     *
     * @param  string $value
     * @return void
     */
    public function setUrlAttribute($value)
    {
        if (!array_key_exists('url', $this->attributes) || $value !== $this->attributes['url']) {
            $this->attributes['last_status'] = null;
        }

        $this->attributes['url'] = $value;
    }

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
     * Generates a slack payload for the link failure.
     *
     * @return array
     */
    public function notificationPayload()
    {
        $message = trans('checkUrls.message', ['link' => $this->title]);

        $payload = [
            'attachments' => [
                [
                    'fallback' => $message,
                    'text'     => $message,
                    'color'    => 'danger',
                    'fields'   => [
                        [
                            'title' => trans('notifications.project'),
                            'value' => sprintf(
                                '<%s|%s>',
                                route('projects', ['id' => $this->project_id]),
                                $this->project->name
                            ),
                            'short' => true,
                        ],
                    ],
                    'footer' => trans('app.name'),
                    'ts'     => time(),
                ],
            ],
        ];

        return $payload;
    }
}
