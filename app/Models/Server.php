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
use Fixhub\Models\Traits\HasTargetable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * Server model.
 */
class Server extends Model
{
    use SoftDeletes, BroadcastChanges, RevisionableTrait, HasTargetable;

    const SUCCESSFUL = 0;
    const UNTESTED   = 1;
    const FAILED     = 2;
    const TESTING    = 3;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'deleted_at', 'pivot', 'environment'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'user', 'enabled', 'ip_address','targetable_id', 'targetable_type',
                           'path', 'status', 'output', 'port', 'order', ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'             => 'integer',
        'enabled'        => 'boolean',
        'targetable_id'  => 'integer',
        'status'         => 'integer',
        'port'           => 'integer',
    ];

    /**
     * Has many relationship.
     *
     * @return ServerLog
     */
    public function logs()
    {
        return $this->hasMany(ServerLog::class, 'server_id', 'id');
    }

    /**
     * Determines whether the server is currently being testing.
     *
     * @return bool
     */
    public function isTesting()
    {
        return ($this->status === self::TESTING);
    }

    /**
     * Define a mutator for the user, if it has changed or has
     * not previously been set also set the status to untested.
     *
     * @param  string $value
     * @return void
     */
    public function setUserAttribute($value)
    {
        $this->setAttributeStatusUntested('user', $value);
    }

    /**
     * Define a mutator for the path, if it has changed or has
     * not previously been set also set the status to untested.
     *
     * @param  string $value
     * @return void
     */
    public function setPathAttribute($value)
    {
        $this->setAttributeStatusUntested('path', $value);
    }

    /**
     * Define a mutator for the IP Address, if it has changed or
     * has not previously been set also set the status to untested.
     *
     * @param  string $value
     * @return void
     */
    public function setIpAddressAttribute($value)
    {
        $this->setAttributeStatusUntested('ip_address', $value);
    }

    /**
     * Define a mutator for the port, if it has changed or
     * has not previously been set also set the status to untested.
     *
     * @param  string $value
     * @return void
     */
    public function setPortAttribute($value)
    {
        $this->setAttributeStatusUntested('port', (int) $value);
    }

    /**
     * Updates the attribute value and if it has changed set the server status to untested.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param void
     */
    private function setAttributeStatusUntested($attribute, $value)
    {
        if (!array_key_exists($attribute, $this->attributes) || $value !== $this->attributes[$attribute]) {
            $this->attributes['status'] = self::UNTESTED;
        }

        $this->attributes[$attribute] = $value;
    }

    /**
     * The server path without a trailing slash.
     *
     * @return string
     */
    public function getCleanPathAttribute()
    {
        return preg_replace('#/$#', '', $this->path);
    }
}
