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
use Piplin\Models\Traits\BroadcastChanges;
use Piplin\Models\Traits\HasTargetable;

/**
 * Server model.
 *
 * @property int $id
 * @property string $name
 * @property string $ip_address
 * @property string $user
 * @property int $port
 * @property int $status
 * @property int $order
 * @property bool $enabled
 * @property int $targetable_id
 * @property string $targetable_type
 * @property string|null $output
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\ServerLog[] $logs
 * @property-read int|null $logs_count
 * @property-read Model|\Eloquent $targetable
 * @method static \Illuminate\Database\Eloquent\Builder|Server newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Server newQuery()
 * @method static \Illuminate\Database\Query\Builder|Server onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Server query()
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereOutput($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereTargetableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereTargetableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereUser($value)
 * @method static \Illuminate\Database\Query\Builder|Server withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Server withoutTrashed()
 * @mixin \Eloquent
 */
class Server extends Model
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

    const SUCCESSFUL = 0;
    const UNTESTED   = 1;
    const FAILED     = 2;
    const TESTING    = 3;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'deleted_at',
        'pivot',
        'environment',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'user',
        'enabled',
        'ip_address',
        'targetable_id',
        'targetable_type',
        'status',
        'output',
        'port',
        'order',
    ];

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
}
