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
 * Model for environment.
 */
class Environment extends Model
{
    use SoftDeletes, BroadcastChanges, HasTargetable, RevisionableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'order', 'default_on'];

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
        'id'         => 'integer',
        'default_on' => 'boolean',
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
     * @return Project
     */
    public function servers()
    {
        return $this->hasMany(Server::class, 'environment_id', 'id');
    }

    /**
     * Belongs to many relationship.
     *
     * @return Server
     */
    
    public function deployments()
    {
        return $this->belongsToMany(Deployment::class)
                    ->orderBy('id', 'DESC');
    }

    /**
     * Belongs to many relationship.
     *
     * @return Environment
     */
    public function opposite_environments()
    {
        return $this->belongsToMany('Fixhub\Models\Environment', 'environment_links', 'environment_id', 'opposite_environment_id');
    }

    /**
     * Belongs to many relationship.
     *
     * @return ConfigFile
     */
    public function configFiles()
    {
        return $this->belongsToMany(ConfigFile::class)
                    ->orderBy('id', 'DESC');
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
            $servers[] = ($key+1) . '. ' . $server->name . ' : ' .$server->ip_address;
        }

        if (count($servers)) {
            return implode("<br />", $servers);
        }

        return trans('app.none');
    }
}
