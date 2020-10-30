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
 * Model for environment.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $order
 * @property int $targetable_id
 * @property string $targetable_type
 * @property bool $default_on
 * @property int $status
 * @property string|null $last_run
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Cabinet[] $cabinets
 * @property-read int|null $cabinets_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Command[] $commands
 * @property-read int|null $commands_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\ConfigFile[] $configFiles
 * @property-read int|null $config_files_count
 * @property-read int $cabinet_count
 * @property-read string $cabinet_names
 * @property-read int $link_count
 * @property-read string $link_names
 * @property-read int $server_count
 * @property-read string $server_names
 * @property-read \Illuminate\Database\Eloquent\Collection|Environment[] $oppositeEnvironments
 * @property-read int|null $opposite_environments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Server[] $servers
 * @property-read int|null $servers_count
 * @property-read Model|\Eloquent $targetable
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|Environment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Environment newQuery()
 * @method static \Illuminate\Database\Query\Builder|Environment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Environment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereDefaultOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereLastRun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereTargetableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereTargetableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Environment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Environment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Environment withoutTrashed()
 * @mixin \Eloquent
 */
class Environment extends Model
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

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
    protected $appends = ['cabinet_count', 'cabinet_names', 'server_count', 'server_names', 'link_count', 'link_names'];

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
     * Belongs to many relationship.
     *
     * @return Cabinet
     */
    public function cabinets()
    {
        return $this->belongsToMany(Cabinet::class)->withPivot(['id', 'status']);
    }

    /**
     * Belongs to many relationship.
     *
     * @return Server
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class)
                    ->orderBy('id', 'DESC');
    }

    /**
     * Belongs to many relationship.
     *
     * @return Command
     */
    public function commands()
    {
        return $this->belongsToMany(Command::class)
                    ->orderBy('order', 'ASC');
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
     * Belongs to many relationship.
     *
     * @return Environment
     */
    public function oppositeEnvironments()
    {
        return $this->belongsToMany(
            self::class,
            'environment_links',
            'environment_id',
            'opposite_environment_id'
        );
    }

    /**
     * Belongs to many relationship.
     *
     * @return Environment
     */
    public function oppositePivot()
    {
        return $this->oppositeEnvironments()->withPivot('link_type');
    }

    /**
     * Define a accessor for the count of links.
     *
     * @return int
     */
    public function getLinkCountAttribute()
    {
        return $this->oppositeEnvironments->count();
    }

    /**
     * Gets the readable list of links.
     *
     * @return string
     */
    public function getLinkNamesAttribute()
    {
        $links = [];
        foreach ($this->oppositePivot as $key => $link) {
            if ($link->pivot->link_type === EnvironmentLink::AUTOMATIC) {
                $link_type = trans('environments.link_auto');
            } else {
                $link_type = trans('environments.link_manual');
            }
            $links[] = ($key + 1) . '. ' . $link->name . ' - ' . $link_type;
        }

        if (count($links)) {
            return implode('<br />', $links);
        }

        return trans('app.none');
    }

    /**
     * Define a accessor for the count of cabinets.
     *
     * @return int
     */
    public function getCabinetCountAttribute()
    {
        return $this->cabinets->count();
    }

    /**
     * Gets the readable list of cabinets.
     *
     * @return string
     */
    public function getCabinetNamesAttribute()
    {
        $cabinets = [];
        foreach ($this->cabinets as $key => $cabinet) {
            $cabinets[] = ($key + 1) . '. ' . $cabinet->name;
        }

        if (count($cabinets)) {
            return implode('<br />', $cabinets);
        }

        return trans('app.none');
    }

    /**
     * Define a accessor for the count of servers.
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
