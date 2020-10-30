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

/**
 * Cabinet model.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $private_key_content
 * @property-read string $public_key_content
 * @property-read int $server_count
 * @property-read string $server_names
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Server[] $servers
 * @property-read int|null $servers_count
 * @method static \Illuminate\Database\Eloquent\Builder|Cabinet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cabinet newQuery()
 * @method static \Illuminate\Database\Query\Builder|Cabinet onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Cabinet query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cabinet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cabinet whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cabinet whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cabinet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cabinet whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cabinet whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cabinet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Cabinet withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Cabinet withoutTrashed()
 * @mixin \Eloquent
 */
class Cabinet extends Model
{
    use SoftDeletes, BroadcastChanges;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'order',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'deleted_at',
        'updated_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Additional attributes to include in the JSON representation.
     *
     * @var array
     */
    protected $appends = [
        'server_count',
        'server_names',
    ];

    /**
     * Has many relationship.
     *
     * @return Server
     */
    public function servers()
    {
        return $this->morphMany(Server::class, 'targetable');
    }

    /**
     * Define an accessor for the public key content.
     *
     * @return string
     */
    public function getPublicKeyContentAttribute()
    {
        return $this->key->public_key;
    }

    /**
     * Define an accessor for the private key content.
     *
     * @return string
     */
    public function getPrivateKeyContentAttribute()
    {
        return $this->key->private_key;
    }

    /**
     * Define a accessor for the count of projects.
     *
     * @return int
     */
    public function getServerCountAttribute()
    {
        return $this->servers->count();
    }

    /**
     * Gets the readable list of servers.
     *
     * @return string
     */
    public function getServerNamesAttribute()
    {
        $servers = [];
        foreach ($this->servers as $key => $server) {
            $servers[] = ($key + 1) . '. ' . $server->name . ' : ' . $server->ip_address;
        }

        if (count($servers)) {
            return implode('<br />', $servers);
        }

        return trans('app.none');
    }
}
