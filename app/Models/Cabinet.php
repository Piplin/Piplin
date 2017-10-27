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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * Cabinet model.
 */
class Cabinet extends Model
{
    use SoftDeletes, BroadcastChanges, RevisionableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'order'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'deleted_at', 'updated_at'];

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
    protected $appends = ['server_count', 'server_names'];

    /**
     * Revision creations enabled.
     *
     * @var boolean
     */
    protected $revisionCreationsEnabled = true;

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
     * Define a accessor for the count of projects.
     *
     * @return int
     */
    public function getServerCountAttribute()
    {
        return 3;
    }

    /**
     * Gets the readable list of servers.
     *
     * @return string
     */
    public function getServerNamesAttribute()
    {
        $servers = [];
        $servers[] = '1. name1' . ' : 127.0.0.1';
        $servers[] = '2. name2' . ' : 127.0.0.2';
        $servers[] = '3. name3' . ' : 127.0.0.3';

        if (count($servers)) {
            return implode("<br />", $servers);
        }

        return trans('app.none');
    }
}
